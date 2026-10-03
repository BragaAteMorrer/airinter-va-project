(() => {
  const eras=['modern','2000','minitel'], serviceCode='3615 AIR INTER', root=document.documentElement;
  const select=document.getElementById('login-era'), code=document.getElementById('minitel-access-code'), gate=document.getElementById('minitel-access'), status=document.getElementById('minitel-access-status');
  const email=document.getElementById('email'), password=document.getElementById('password'), form=document.querySelector('.login-form');
  const controls=[...document.querySelectorAll('[data-auth-control]')], external=[...document.querySelectorAll('[data-auth-external]')];
  const valid=()=>code?.value.trim().replace(/\s+/g,' ').toUpperCase()===serviceCode;
  const messages=window.prometheeLoginI18n||{};
  const minitelMode=()=>root.dataset.loginEra==='minitel';
  const announce=(message,color='#fff35a')=>{if(status){status.textContent=message;status.style.color=color}};
  const update=()=>{const locked=minitelMode()&&!valid();controls.forEach(control=>control.disabled=locked);external.forEach(link=>link.hidden=locked);if(status){status.textContent=locked?(messages.minitelLocked||'TAPEZ 3615 AIR INTER PUIS ENVOI'):(messages.minitelOpen||'SERVICE OUVERT - ENVOI POUR CONTINUER');status.style.color=locked?'#fff35a':'#39ff4a'}};
  const setEra=era=>{if(!eras.includes(era))era='modern';root.dataset.loginEra=era;if(select)select.value=era;if(gate)gate.hidden=era!=='minitel';try{localStorage.setItem('promethee-era',era)}catch{}update();if(era==='minitel'&&code)code.focus()};
  let stored='modern';try{stored=localStorage.getItem('promethee-era')||stored}catch{}setEra(stored);select?.addEventListener('change',event=>setEra(event.target.value));code?.addEventListener('input',update);

  document.addEventListener('keydown',event=>{
    if(!minitelMode())return;
    const active=document.activeElement;
    if(event.key==='F1'){
      event.preventDefault();event.stopPropagation();
      announce('GUIDE : ENVOI=VALIDER  ESC=ANNULER  F10=FIN','#58f7ff');
      code?.focus();return;
    }
    if(event.key==='Home'){
      event.preventDefault();event.stopPropagation();
      code?.focus();return;
    }
    if(event.key==='F10'||event.key==='End'){
      event.preventDefault();event.stopPropagation();
      setEra('modern');return;
    }
    if(event.key==='Escape'&&active instanceof HTMLInputElement){
      event.preventDefault();event.stopPropagation();
      active.value='';update();return;
    }
    if(event.key==='Enter'||event.code==='NumpadEnter'){
      if(active===code){
        event.preventDefault();event.stopPropagation();
        if(valid()){update();email?.focus()}else announce('CODE INVALIDE - 3615 AIR INTER','#ff2028');
        return;
      }
      if(active===email){
        event.preventDefault();event.stopPropagation();
        password?.focus();return;
      }
      if(active===password){
        event.preventDefault();event.stopPropagation();
        if(!password?.disabled)form?.requestSubmit();return;
      }
    }
  },true);
  const installAppearanceControl=()=>{
    const topbar=document.querySelector('.login-topbar'); if(!topbar||document.getElementById('login-appearance'))return;
    const label=document.createElement('label'); label.className='login-era login-appearance'; label.textContent=`${messages.appearance||'Appearance'} `;
    const control=document.createElement('select'); control.id='login-appearance'; control.setAttribute('aria-label','Appearance');
    control.innerHTML=`<option value="light">${messages.appearanceLight||'Day'}</option><option value="dark">${messages.appearanceDark||'Night'}</option>`; label.append(control); topbar.append(label);
    const setAppearance=(appearance,persist=true)=>{if(!['light','dark'].includes(appearance))appearance='light';root.dataset.appearance=appearance;control.value=appearance;if(persist)try{localStorage.setItem('promethee-appearance',appearance)}catch{}};
    let saved;try{saved=localStorage.getItem('promethee-appearance')}catch{}setAppearance(['light','dark'].includes(saved)?saved:(matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'),false);
    control.addEventListener('change',event=>setAppearance(event.target.value));
  };
  installAppearanceControl();
})();
