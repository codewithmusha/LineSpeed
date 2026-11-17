/* LineSpeed — real browser-to-server measurements, no synthetic scores. */
(() => {
  'use strict';
  const panel = document.querySelector('.test-panel');
  if (!panel) return;
  const token = panel.dataset.testToken;
  const t = (key) => window.LineI18n?.t(key) || key;
  const $ = (id) => document.getElementById(id);
  const refs = {
    start: $('test-button'), label: $('button-label'), save: $('download-button'),
    speed: $('speed-value'), progress: $('gauge-progress'),
    goWord: $('go-word'), speedUnit: $('speed-unit'), connectionLabel: $('connection-label'),
    status: $('test-status'), detail: $('test-detail'), dot: $('status-dot'),
    download: $('download-value'), upload: $('upload-value'),
    ping: $('ping-value'), jitter: $('jitter-value'), stamp: $('last-tested')
  };
  let running = false;
  let cancelRequested = false;
  let activeController = null;
  let activeXHR = null;
  let latest = null;
  let network = { provider: null, country: null, source: null };
  let activePhase = 'idle';

  const round = (value, digits = 1) => Number.isFinite(value) ? value.toFixed(digits) : '—';
  const pause = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
  const mbps = (bytes, elapsedMs) => (bytes * 8) / Math.max(elapsedMs, 1) / 1000;
  const setGauge = (value) => {
    const safe = Math.max(0, Number.isFinite(value) ? value : 0);
    refs.speed.textContent = round(safe);
    const percent = Math.min(100, (Math.log1p(safe) / Math.log1p(1000)) * 100);
    refs.progress.style.strokeDashoffset = String(100 - percent);
  };
  function setStatus(headline, detail) {
    refs.status.textContent = headline;
    refs.detail.textContent = detail;
  }
  function reset() {
    latest = null;
    panel.classList.remove('is-complete', 'has-error');
    setPhase('idle');
    setGauge(0);
    refs.ping.textContent = '—';
    refs.jitter.textContent = '—';
    refs.download.textContent = '—';
    refs.upload.textContent = '—';
    refs.save.disabled = true;
    ['streaming','calls','gaming'].forEach(name=>{const verdict=$(`${name}-verdict`),detail=$(`${name}-detail`);if(verdict){verdict.dataset.i18n='testFirst';verdict.textContent=t('testFirst');}if(detail){detail.dataset.i18n=name==='streaming'?'streamingDesc':name==='calls'?'callsDesc':'gamingDesc';detail.textContent=t(detail.dataset.i18n);}});
    refs.stamp.textContent = t('phaseWorking');
  }
  function abortCurrent() {
    if (activeController) activeController.abort();
    if (activeXHR) activeXHR.abort();
  }
  function updateButton() {
    const displayingSpeed = running || Boolean(latest);
    panel.classList.toggle('is-running', running);
    panel.classList.toggle('is-complete', !running && Boolean(latest));
    refs.goWord.hidden = displayingSpeed;
    refs.speed.hidden = !displayingSpeed;
    refs.speedUnit.hidden = !displayingSpeed;
    refs.label.textContent = running ? t('stopTest') : latest ? t('again') : t('startTest');
    refs.start.setAttribute('aria-label', running ? 'Stop speed test' : latest ? 'Run speed test again' : 'Start internet speed test');
    refs.connectionLabel.textContent = running ? t('phaseWorking') : latest ? t('phaseComplete') : t('ready');
    refs.dot.classList.toggle('testing', running);
  }
  function setPhase(phase) {
    panel.dataset.phase = phase;
    activePhase = phase;
    const order = ['ping', 'download', 'upload'];
    const index = order.indexOf(phase);
    document.querySelectorAll('.test-step').forEach((item) => {
      const itemIndex = order.indexOf(item.dataset.step);
      const completed = phase === 'complete' || (index >= 0 && itemIndex < index);
      item.classList.toggle('is-active', itemIndex === index);
      item.classList.toggle('is-done', completed);
      item.querySelector('.step-check').textContent = completed ? '✓' : String(itemIndex + 1);
    });
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
    setPhase('ping');
    setStatus(t('pingStatus'), t('pingDetail'));
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
      setPhase('download');
      setStatus(t('downStatus'), t('downDetail'));
      const firstDown = await downloadSample(1);
      const downSizeMB = Math.max(2, Math.min(16, Math.ceil(firstDown.speed * 1.4 / 8)));
      setStatus(t('downStatus'), t('downDetail') + ` · ${downSizeMB} MB`);
      const down = await downloadSample(downSizeMB);
      refs.download.textContent = round(down.speed);
      setGauge(down.speed);
      if (cancelRequested) throw new Error('Test cancelled.');
      setPhase('upload');
      setStatus(t('upStatus'), t('upDetail'));
      const firstUp = await uploadSample(1);
      const upSizeMB = Math.max(2, Math.min(5, Math.ceil(firstUp.speed * 1.25 / 8)));
      setStatus(t('upStatus'), t('upDetail') + ` · ${upSizeMB} MB`);
      const up = await uploadSample(upSizeMB);
      refs.upload.textContent = round(up.speed);
      if (cancelRequested) throw new Error('Test cancelled.');
      setGauge(down.speed);
      latest = { download: down.speed, upload: up.speed, ping: latency.ping, jitter: latency.jitter, timestamp: new Date() };
      setGauge(down.speed);
      setPhase('complete');
      setStatus(t('completeStatus'), t('completeDetail'));
      renderQuality();
      refs.stamp.textContent = t('justNow');
      refs.save.disabled = false;
    } catch (e) {
      setGauge(0);
      setPhase('idle');
      panel.classList.add('has-error');
      refs.save.disabled = true;
      refs.dot.classList.add('error');
      setStatus(cancelRequested ? t('cancelled') : t('failed'), e?.message || 'Please try again.');
      refs.stamp.textContent = t('noResult');
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
      refs.label.textContent = t('stopping');
    } else { void runTest(); }
  });

  function roundedRect(ctx, x, y, w, h, radius, color) {
    ctx.fillStyle = color;
    ctx.beginPath();
    if (ctx.roundRect) ctx.roundRect(x, y, w, h, radius);
    else ctx.rect(x, y, w, h);
    ctx.fill();
  }
  function fitCanvasText(ctx, value, maxWidth) {
    let s=String(value||'—');
    while(s.length>4 && ctx.measureText(s).width>maxWidth)s=s.slice(0,-2)+'…';
    return s;
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
      const ink = '#1b2822', gold = '#e9bb63', muted = '#637268';
      ctx.fillStyle = '#f7f7f2'; ctx.fillRect(0, 0, 1600, 900);
      roundedRect(ctx, 70, 60, 1460, 776, 22, '#ffffff');
      roundedRect(ctx, 70, 60, 1460, 196, 22, ink);
      ctx.fillStyle = gold; ctx.fillRect(70, 221, 1460, 35);
      ctx.fillStyle = gold; ctx.font = '700 30px Poppins, sans-serif'; ctx.fillText('▮ ▮ ▮', 124, 139);
      ctx.fillStyle = '#f8f9f4'; ctx.font = '700 42px Poppins, sans-serif'; ctx.fillText('LineSpeed.', 250, 141);
      ctx.font = '500 20px Poppins, sans-serif'; ctx.fillStyle = '#e3e8dc'; ctx.fillText('CONNECTION REPORT', 1120, 140);
      ctx.fillStyle=ink; ctx.font='700 55px Poppins, sans-serif';ctx.fillText('Your internet, in numbers.',124,340);
      ctx.fillStyle=muted; ctx.font='400 20px Poppins, sans-serif'; ctx.fillText('A real browser-to-server measurement.',125,382);
      roundedRect(ctx,124,425,745,240,14,'#f4f1e7');
      ctx.fillStyle='#8b632c';ctx.font='700 19px Poppins, sans-serif';ctx.fillText('DOWNLOAD SPEED',164,478);
      ctx.fillStyle=ink;ctx.font='700 128px Poppins, sans-serif';ctx.fillText(round(latest.download),151,603);
      const mainWidth=ctx.measureText(round(latest.download)).width;
      ctx.fillStyle='#8b632c';ctx.font='600 27px Poppins, sans-serif';ctx.fillText('Mbps',Math.min(160+mainWidth+20,739),601);
      const tiles=[
        {label:'UPLOAD',value:`${round(latest.upload)} Mbps`,x:912,y:428},
        {label:'PING',value:`${round(latest.ping,0)} ms`,x:912,y:508},
        {label:'JITTER',value:`${round(latest.jitter)} ms`,x:912,y:588}
      ];
      tiles.forEach(tile=>{
        ctx.strokeStyle='#dbe1d8';ctx.lineWidth=2;ctx.beginPath();ctx.moveTo(tile.x,tile.y);ctx.lineTo(1455,tile.y);ctx.stroke();
        ctx.fillStyle=muted;ctx.font='600 18px Poppins, sans-serif';ctx.fillText(tile.label,tile.x,tile.y+41);
        ctx.fillStyle=ink;ctx.font='700 29px Poppins, sans-serif';ctx.fillText(tile.value,tile.x+177,tile.y+42);
      });
      ctx.strokeStyle='#dbe1d8';ctx.beginPath();ctx.moveTo(124,698);ctx.lineTo(1474,698);ctx.stroke();
      ctx.fillStyle=muted;ctx.font='500 18px Poppins, sans-serif';ctx.fillText('NETWORK',124,749);ctx.fillText('DEVICE',865,749);
      ctx.fillStyle=ink;ctx.font='600 23px Poppins, sans-serif';
      ctx.fillText(fitCanvasText(ctx,network.provider||'Not available',560),124,785);
      ctx.fillText(fitCanvasText(ctx,deviceInfo.label,520),865,785);
      ctx.fillStyle=muted;ctx.font='400 16px Poppins, sans-serif';
      ctx.fillText(latest.timestamp.toLocaleString(undefined,{dateStyle:'medium',timeStyle:'short'}),125,820);
      ctx.fillText('Estimates vary by server, network and device',1020,820);
      const blob = await new Promise(resolve => canvas.toBlob(resolve,'image/png'));
      if (!blob) throw new Error('Could not create image.');
      const url=URL.createObjectURL(blob);
      const a=document.createElement('a');a.href=url;a.download=`linespeed-result-${latest.timestamp.toISOString().slice(0,10)}.png`;
      document.body.appendChild(a);a.click();a.remove();
      setTimeout(()=>URL.revokeObjectURL(url),15000);
    } catch(e) { setStatus(t('saveFailed'),e?.message||'Please try again.'); }
    finally {refs.save.disabled=false;}
  }
  function detectDevice() {
    const ua=navigator.userAgent || '';
    const touch=navigator.maxTouchPoints || 0;
    if (/iPad|Tablet|PlayBook|Silk/i.test(ua)||(/Macintosh/i.test(ua)&&touch>1))return {key:'tablet',label:'Tablet'};
    if (/Mobi|iPhone|iPod/i.test(ua)||(/Android/i.test(ua)&&/Mobile/i.test(ua)))return {key:'phone',label:'Phone'};
    // Distinguishing laptop from desktop is not reliable from browser data.
    return {key:'desktop',label:'Computer'};
  }
  const deviceInfo = detectDevice();
  function showDevice() { const el=$('device-name');if(el)el.textContent=deviceInfo.key==='desktop'?t('unknownDevice'):t(deviceInfo.key); }
  async function showNetwork(force=false) {
    const isp=$('isp-name'),country=$('country-name'),credit=$('network-credit'),button=$('network-refresh');
    if(!isp||!country)return;
    if(button)button.disabled=true;
    isp.textContent=t('detecting');
    try{
      const res=await fetch('/api/network.php'+(force?'?refresh=1':''),{cache:'no-store',credentials:'same-origin',headers:{Accept:'application/json'}});
      if(!res.ok)throw new Error('Network lookup unavailable');
      const data=await res.json();
      network={provider:typeof data.provider==='string'?data.provider:null,country:typeof data.country==='string'?data.country:null,source:data.source};
      isp.textContent=network.provider||t('networkUnavailable');
      country.textContent=network.country||t('unknownCountry');
      if(credit)credit.hidden=network.source!=='IPinfo';
    } catch(e){network={provider:null,country:null,source:null};isp.textContent=t('networkUnavailable');country.textContent=t('unknownCountry');if(credit)credit.hidden=true;}
    finally{if(button)button.disabled=false;}
  }
  function renderQuality() {
    if(!latest)return;
    const down=latest.download,up=latest.upload,ping=latest.ping,jitter=latest.jitter;
    const stream=down>=25?['qualityStrong','stream4k']:down>=5?['qualityFair','streamHD']:['qualityLow','streamLow'];
    const call=up>=3&&down>=3&&ping<120?['qualityStrong','callsGood']:up>=1&&down>=1?['qualityFair','callsFair']:['qualityLow','callsLow'];
    const game=ping<55&&jitter<20?['qualityStrong','gameGood']:ping<120&&jitter<45?['qualityFair','gameFair']:['qualityLow','gameLow'];
    [[stream,'streaming'],[call,'calls'],[game,'gaming']].forEach(([pair,name])=>{
      const verdict=$(`${name}-verdict`),detail=$(`${name}-detail`);
      if(verdict){verdict.removeAttribute('data-i18n');verdict.textContent=t(pair[0]);}
      if(detail){detail.removeAttribute('data-i18n');detail.textContent=t(pair[1]);}
    });
  }
  $('network-refresh')?.addEventListener('click',()=>{void showNetwork(true)});
  document.addEventListener('linespeed:language',()=>{
    updateButton();showDevice();
    if(!network.provider&&$('isp-name'))$('isp-name').textContent=t('networkUnavailable');
    if(!network.country&&$('country-name'))$('country-name').textContent=t('unknownCountry');
    if(latest){setStatus(t('completeStatus'),t('completeDetail'));renderQuality();}
    else if(running){const a=activePhase==='ping'?'pingStatus':activePhase==='upload'?'upStatus':'downStatus';const b=activePhase==='ping'?'pingDetail':activePhase==='upload'?'upDetail':'downDetail';setStatus(t(a),t(b));}
    else if(!panel.classList.contains('has-error')){setStatus(t('readyWhen'),t('tapGo'));}
  });
  showDevice();
  void showNetwork();
  refs.save.addEventListener('click', () => { void saveImage(); });
  updateButton();
})();
