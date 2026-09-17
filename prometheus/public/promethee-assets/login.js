(() => {
  const eras=['modern','2000','minitel'], serviceCode='3615 AIR INTER', root=document.documentElement;
  const select=document.getElementById('login-era'), code=document.getElementById('minitel-access-code'), gate=document.getElementById('minitel-access'), status=document.getElementById('minitel-access-status');
  const controls=[...document.querySelectorAll('[data-auth-control]')], external=[...document.querySelectorAll('[data-auth-external]')];
  const valid=()=>code?.value.trim().replace(/\s+/g,' ').toUpperCase()===serviceCode;
  const messages=window.prometheeLoginI18n||{};
  const update=()=>{const locked=root.dataset.loginEra==='minitel'&&!valid();controls.forEach(control=>control.disabled=locked);external.forEach(link=>link.hidden=locked);if(status){status.textContent=locked?(messages.minitelLocked||''):(messages.minitelOpen||'');status.style.color=locked?'#f0f':'#0f0'}};
  const setEra=era=>{if(!eras.includes(era))era='modern';root.dataset.loginEra=era;if(select)select.value=era;if(gate)gate.hidden=era!=='minitel';try{localStorage.setItem('promethee-era',era)}catch{}update();if(era==='minitel'&&code)code.focus()};
  let stored='modern';try{stored=localStorage.getItem('promethee-era')||stored}catch{}setEra(stored);select?.addEventListener('change',event=>setEra(event.target.value));code?.addEventListener('input',update);
})();
