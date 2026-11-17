/* LineSpeed — real browser-to-server measurements, no synthetic scores. */
(() => {
  'use strict';
  const panel = document.querySelector('.test-panel');
  if (!panel) return;
  const token = panel.dataset.testToken;
  const $ = (id) => document.getElementById(id);
  const refs = {
    start: $('test-button'), label: $('button-label'), save: $('download-button'),
    speed: $('speed-value'), progress: $('gauge-progress'),
    status: $('test-status'), detail: $('test-detail'), dot: $('status-dot'),
    download: $('download-value'), upload: $('upload-value'),
    ping: $('ping-value'), jitter: $('jitter-value'), stamp: $('last-tested')
  };
  let running = false;
  let cancelRequested = false;
  let activeController = null;
  let activeXHR = null;
  let latest = null;

  const round = (value, digits = 1) => Number.isFinite(value) ? value.toFixed(digits) : '—';
  const pause = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
  const mbps = (bytes, elapsedMs) => (bytes * 8) / Math.max(elapsedMs, 1) / 1000;
  const setGauge = (value) => {
    const safe = Math.max(0, Number.isFinite(value) ? value : 0);
    refs.speed.textContent = round(safe);
    const percent = Math.min(100, (Math.log1p(safe) / Math.log1p(1000)) * 100);
    refs.progress.style.strokeDasharray = `${percent} 100`;
  };
  function setStatus(headline, detail) {
    refs.status.textContent = headline;
    refs.detail.textContent = detail;
  }
  function reset() {
    latest = null;
    setGauge(0);
    refs.ping.textContent = '—';
    refs.jitter.textContent = '—';
    refs.download.textContent = '—';
    refs.upload.textContent = '—';
    refs.save.disabled = true;
    refs.stamp.textContent = 'TEST IN PROGRESS';
  }
  function abortCurrent() {
    if (activeController) activeController.abort();
    if (activeXHR) activeXHR.abort();
  }
  function updateButton() {
    refs.label.textContent = running ? 'Cancel test' : latest ? 'Run test again' : 'Start speed test';
    refs.dot.classList.toggle('testing', running);
  }
  function getAbortableFetch(url) {
    const controller = new AbortController();
    activeController = controller;
    let timedOut = false;
    const timeout = setTimeout(() => { timedOut = true; controller.abort(); }, 35000);
    const promise = fetch(url, {
      method: 'GET', credentials: 'same-origin', cache: 'no-store',
      headers: { 'X-Test-Token': token, 'Accept': '*/*' }, signal: controller.signal
    });
    return {
      promise,
      close: () => { clearTimeout(timeout); if (activeController === controller) activeController = null; },
      timedOut: () => timedOut,
      signal: controller.signal
    };
  }
  async function checkPing() {
    const readings = [];
    setStatus('Checking responsiveness', 'Measuring ping and jitter');
    for (let i = 0; i < 7; i++) {
      if (cancelRequested) throw new Error('Test cancelled.');
      const operation = getAbortableFetch(`/api/ping.php?t=${Date.now()}-${i}`);
      const start = performance.now();
      try {
        const response = await operation.promise;
        if (!response.ok) throw new Error(await errorText(response));
        await response.text();
        const elapsed = performance.now() - start;
        if (i > 0) {
          readings.push(elapsed); // The first request is a warm-up.
          refs.ping.textContent = round(median(readings), 0);
        }
      } catch (e) {
        if (operation.timedOut()) throw new Error('The connection timed out during ping testing.');
        throw e;
      } finally { operation.close(); }
      if (i < 6) await pause(100);
    }
    const jitter = readings.slice(1).reduce((sum, sample, i) => sum + Math.abs(sample - readings[i]), 0) / (readings.length - 1);
    refs.jitter.textContent = round(jitter, 1);
    return { ping: median(readings), jitter };
  }
  function median(values) {
    const sorted = [...values].sort((a, b) => a - b);
    const middle = Math.floor(sorted.length / 2);
    return sorted.length % 2 ? sorted[middle] : (sorted[middle - 1] + sorted[middle]) / 2;
  }
  async function errorText(response) {
    try { const json = await response.json(); return json.error || `Server returned HTTP ${response.status}.`; }
    catch { return `Server returned HTTP ${response.status}.`; }
  }
  async function downloadSample(sizeMB) {
    const operation = getAbortableFetch(`/api/download.php?mb=${sizeMB}&t=${Date.now()}`);
    const start = performance.now();
    let bytes = 0;
    try {
      const response = await operation.promise;
      if (!response.ok) throw new Error(await errorText(response));
      const reader = response.body?.getReader();
      if (!reader) {
        const buffer = await response.arrayBuffer();
        bytes = buffer.byteLength;
      } else {
        while (true) {
          const { done, value } = await reader.read();
          if (done) break;
          bytes += value.byteLength;
          const current = mbps(bytes, performance.now() - start);
          setGauge(current);
        }
      }
      if (bytes !== sizeMB * 1024 * 1024) throw new Error('Download test was incomplete. Please try again.');
      return { speed: mbps(bytes, performance.now() - start), bytes };
    } catch (e) {
      if (operation.timedOut()) throw new Error('Download took too long. Please try with a more stable connection.');
      throw e;
    } finally { operation.close(); }
  }
  function createPayload(sizeMB) {
    const buffer = new Uint8Array(sizeMB * 1024 * 1024);
    // Random payload prevents compression from distorting upload results.
    for (let i = 0; i < buffer.length; i += 65536) {
      crypto.getRandomValues(buffer.subarray(i, Math.min(i + 65536, buffer.length)));
    }
    return new Blob([buffer], { type: 'application/octet-stream' });
  }
  function uploadSample(sizeMB) {
    return new Promise((resolve, reject) => {
      if (cancelRequested) return reject(new Error('Test cancelled.'));
      const payload = createPayload(sizeMB);
      const xhr = new XMLHttpRequest();
      activeXHR = xhr;
      xhr.open('POST', `/api/upload.php?t=${Date.now()}`);
      xhr.setRequestHeader('X-Test-Token', token);
      xhr.setRequestHeader('Content-Type', 'application/octet-stream');
      xhr.responseType = 'json';
      xhr.timeout = 35000;
      let beganAt = 0;
      let uploadEndAt = 0;
      xhr.upload.onload = () => { uploadEndAt = performance.now(); };
      xhr.upload.onprogress = (event) => {
        if (event.lengthComputable && beganAt > 0) {
          const now = performance.now();
          // The upload estimate updates the gauge as data is transferred.
          setGauge(mbps(event.loaded, now - beganAt));
        }
      };
      const cleanup = () => { if (activeXHR === xhr) activeXHR = null; };
      xhr.onload = () => {
        cleanup();
        if (xhr.status !== 200 || !xhr.response?.ok) {
          reject(new Error(xhr.response?.error || `Upload server returned HTTP ${xhr.status}.`));
          return;
        }
        const confirmed = Number(xhr.response.received_bytes);
        if (confirmed !== payload.size) return reject(new Error('Upload test was incomplete.'));
        const finished = uploadEndAt || performance.now();
        resolve({ speed: mbps(payload.size, finished - beganAt), bytes: payload.size });
      };
      xhr.onerror = () => { cleanup(); reject(new Error('Upload failed. Check your connection or hosting limits.')); };
      xhr.onabort = () => { cleanup(); reject(new Error('Test cancelled.')); };
      xhr.ontimeout = () => { cleanup(); reject(new Error('Upload took too long. Please try again.')); };
      beganAt = performance.now();
      xhr.send(payload);
    });
  }
  async function runTest() {
    running = true;
    cancelRequested = false;
    reset();
    refs.dot.classList.remove('error');
    updateButton();
    try {
      const latency = await checkPing();
      if (cancelRequested) throw new Error('Test cancelled.');
      setStatus('Testing download speed', 'Receiving a small data sample');
      const firstDown = await downloadSample(1);
      const downSizeMB = Math.max(2, Math.min(16, Math.ceil(firstDown.speed * 1.4 / 8)));
      setStatus('Testing download speed', `Measuring a ${downSizeMB} MB sample`);
      const down = await downloadSample(downSizeMB);
      refs.download.textContent = round(down.speed);
      setGauge(down.speed);
      if (cancelRequested) throw new Error('Test cancelled.');
      setStatus('Testing upload speed', 'Sending a small data sample');
      const firstUp = await uploadSample(1);
      const upSizeMB = Math.max(2, Math.min(5, Math.ceil(firstUp.speed * 1.25 / 8)));
      setStatus('Testing upload speed', `Measuring a ${upSizeMB} MB sample`);
      const up = await uploadSample(upSizeMB);
      refs.upload.textContent = round(up.speed);
      if (cancelRequested) throw new Error('Test cancelled.');
      setGauge(down.speed);
      latest = { download: down.speed, upload: up.speed, ping: latency.ping, jitter: latency.jitter, timestamp: new Date() };
      setStatus('Your results are ready', 'Download or share an image of your test');
      refs.stamp.textContent = 'COMPLETED JUST NOW';
      refs.save.disabled = false;
    } catch (e) {
      setGauge(0);
      refs.save.disabled = true;
      refs.dot.classList.add('error');
      setStatus(cancelRequested ? 'Test cancelled' : 'Test could not finish', e?.message || 'Please try again.');
      refs.stamp.textContent = 'NO COMPLETE RESULT';
    } finally {
      running = false;
      activeController = null;
      activeXHR = null;
      updateButton();
    }
  }
  refs.start.addEventListener('click', () => {
    if (running) {
      cancelRequested = true;
      abortCurrent();
      refs.label.textContent = 'Cancelling…';
    } else { void runTest(); }
  });

  function roundedRect(ctx, x, y, w, h, radius, color) {
    ctx.fillStyle = color;
    ctx.beginPath();
    if (ctx.roundRect) ctx.roundRect(x, y, w, h, radius);
    else ctx.rect(x, y, w, h);
    ctx.fill();
  }
  async function saveImage() {
    if (!latest) return;
    refs.save.disabled = true;
    try {
      if (document.fonts?.ready) await document.fonts.ready;
      const canvas = document.createElement('canvas');
      canvas.width = 1600; canvas.height = 900;
      const ctx = canvas.getContext('2d');
      if (!ctx) throw new Error('This browser cannot create the image.');
      const ink = '#202b26', pine = '#234b3c', clay = '#c77750', muted = '#7b8980';
      ctx.fillStyle = '#f7f6f1'; ctx.fillRect(0, 0, 1600, 900);
      roundedRect(ctx, 72, 62, 1456, 776, 22, '#ffffff');
      roundedRect(ctx, 128, 125, 55, 55, 11, pine);
      ctx.fillStyle = '#ffffff'; ctx.font = '700 35px Poppins, sans-serif'; ctx.fillText('↗', 141, 164);
      ctx.fillStyle = ink; ctx.font = '700 36px Poppins, sans-serif'; ctx.fillText('LineSpeed', 203, 166);
      ctx.fillStyle = clay; ctx.fillText('.', 393, 166);
      ctx.fillStyle = muted; ctx.font = '600 18px Poppins, sans-serif'; ctx.fillText('CONNECTION REPORT', 1150, 159);
      ctx.strokeStyle = '#e2e8e1'; ctx.lineWidth = 2; ctx.beginPath(); ctx.moveTo(128, 205); ctx.lineTo(1472, 205); ctx.stroke();
      ctx.fillStyle = ink; ctx.font = '700 64px Poppins, sans-serif'; ctx.fillText('Your internet, in numbers.', 127, 294);
      ctx.fillStyle = muted; ctx.font = '400 22px Poppins, sans-serif'; ctx.fillText('A snapshot of your connection to our test server.', 128, 341);
      roundedRect(ctx, 128, 388, 814, 294, 14, '#eef1e9');
      ctx.fillStyle = pine; ctx.font = '700 21px Poppins, sans-serif'; ctx.fillText('DOWNLOAD SPEED', 170, 442);
      ctx.fillStyle = ink; ctx.font = '700 150px Poppins, sans-serif'; ctx.fillText(round(latest.download), 162, 601);
      const mainWidth = ctx.measureText(round(latest.download)).width;
      ctx.fillStyle = pine; ctx.font = '600 29px Poppins, sans-serif'; ctx.fillText('Mbps', Math.min(168 + mainWidth + 19, 780), 599);
      const tiles = [
        { label: 'UPLOAD', value: `${round(latest.upload)} Mbps`, x: 982, y: 388 },
        { label: 'PING', value: `${round(latest.ping, 0)} ms`, x: 982, y: 493 },
        { label: 'JITTER', value: `${round(latest.jitter)} ms`, x: 982, y: 598 }
      ];
      tiles.forEach((tile) => {
        ctx.strokeStyle = '#e5e8e2'; ctx.lineWidth = 2; ctx.beginPath(); ctx.moveTo(tile.x, tile.y); ctx.lineTo(1465, tile.y); ctx.stroke();
        ctx.fillStyle = muted; ctx.font = '700 18px Poppins, sans-serif'; ctx.fillText(tile.label, tile.x, tile.y + 36);
        ctx.fillStyle = ink; ctx.font = '700 37px Poppins, sans-serif'; ctx.fillText(tile.value, tile.x + 210, tile.y + 40);
      });
      ctx.strokeStyle = '#e2e8e1'; ctx.beginPath(); ctx.moveTo(128, 715); ctx.lineTo(1472, 715); ctx.stroke();
      const when = latest.timestamp.toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
      ctx.fillStyle = muted; ctx.font = '500 18px Poppins, sans-serif'; ctx.fillText(`TESTED  ${when}`, 128, 770);
      ctx.fillText('Browser-to-server estimate · Results can vary', 790, 770);
      const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/png'));
      if (!blob) throw new Error('Could not create image.');
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url; a.download = `linespeed-result-${latest.timestamp.toISOString().slice(0, 10)}.png`;
      document.body.appendChild(a); a.click(); a.remove();
      setTimeout(() => URL.revokeObjectURL(url), 15000);
    } catch (e) {
      setStatus('Could not save image', e?.message || 'Please try again.');
    } finally { refs.save.disabled = false; }
  }
  refs.save.addEventListener('click', () => { void saveImage(); });
})();
