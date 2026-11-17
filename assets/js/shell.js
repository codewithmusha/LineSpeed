(() => {
  'use strict';
  const updateThemeLabel = () => {
    const b=document.getElementById('theme-toggle');
    if (!b) return;
    const light=document.documentElement.dataset.theme==='light';
    b.setAttribute('aria-label',light?'Switch to dark mode':'Switch to light mode');
    b.title=light?'Dark mode':'Light mode';
  };
  if (document.readyState==='loading') document.addEventListener('DOMContentLoaded', start);
  else start();
  function start(){
    updateThemeLabel();
    document.getElementById('theme-toggle')?.addEventListener('click',()=>{
      const next=document.documentElement.dataset.theme==='light'?'dark':'light';
      document.documentElement.dataset.theme=next;
      try{localStorage.setItem('ls-theme',next)}catch(e){}
      updateThemeLabel();
    });
    const menu=document.getElementById('explore-menu');
    if(menu){
      document.addEventListener('click',e=>{if(menu.open&&!menu.contains(e.target))menu.open=false});
      document.addEventListener('keydown',e=>{if(e.key==='Escape'&&menu.open){menu.open=false;menu.querySelector('summary')?.focus()}});
    }
  }
})();
