(() => {
  'use strict';

  const messages = {
    fr: {
      'nav.connect':'Connexion','nav.flight':'Mes opérations','nav.record':'Enregistrement','nav.map':'Suivi du vol',
      'nav.journal':'Journal','nav.datalink':'Datalink','nav.network':'Air Inter Network','nav.review':'Flight Review','nav.settings':'Paramètres',
      'display.language':'Langue','display.style':'Style','display.modern':'Moderne','display.2000':'Années 2000','display.appearance':'Apparence','display.light':'Clair','display.dark':'Nuit',
      'header.direction':'DIRECTION DE L’EXPLOITATION AÉRIENNE','header.subtitle':'Le client ACARS officiel d’Air Inter VA.',
      'auth.crew':'Accès équipage','auth.title':'Connexion pilote','auth.hint':'Identifiez-vous avec votre compte pilote Air Inter pour préparer et suivre votre vol.',
      'auth.login':'Identifiant pilote ou e-mail','auth.password':'Mot de passe','auth.submit':'Se connecter à Air Inter'
    },
    en: {
      'nav.connect':'Sign in','nav.flight':'My operations','nav.record':'Recording','nav.map':'Flight tracking',
      'nav.journal':'Logbook','nav.datalink':'Datalink','nav.network':'Air Inter Network','nav.review':'Flight Review','nav.settings':'Settings',
      'display.language':'Language','display.style':'Style','display.modern':'Modern','display.2000':'2000s','display.appearance':'Appearance','display.light':'Light','display.dark':'Night',
      'header.direction':'FLIGHT OPERATIONS DEPARTMENT','header.subtitle':'The official Air Inter VA ACARS client.',
      'auth.crew':'Crew access','auth.title':'Pilot sign in','auth.hint':'Sign in with your Air Inter pilot account to prepare and track your flight.',
      'auth.login':'Pilot ID or email','auth.password':'Password','auth.submit':'Sign in to Air Inter'
    },
    pt: {
      'nav.connect':'Iniciar sessão','nav.flight':'As minhas operações','nav.record':'Registo','nav.map':'Acompanhamento do voo',
      'nav.journal':'Diário','nav.datalink':'Datalink','nav.network':'Air Inter Network','nav.review':'Revisão do voo','nav.settings':'Definições',
      'display.language':'Idioma','display.style':'Estilo','display.modern':'Moderno','display.2000':'Anos 2000','display.appearance':'Aparência','display.light':'Claro','display.dark':'Noite',
      'header.direction':'DIREÇÃO DE OPERAÇÕES DE VOO','header.subtitle':'O cliente ACARS oficial da Air Inter VA.',
      'auth.crew':'Acesso da tripulação','auth.title':'Início de sessão do piloto','auth.hint':'Inicie sessão com a sua conta de piloto Air Inter para preparar e acompanhar o voo.',
      'auth.login':'ID de piloto ou e-mail','auth.password':'Palavra-passe','auth.submit':'Iniciar sessão na Air Inter'
    },
    es: {
      'nav.connect':'Iniciar sesión','nav.flight':'Mis operaciones','nav.record':'Registro','nav.map':'Seguimiento del vuelo',
      'nav.journal':'Diario','nav.datalink':'Datalink','nav.network':'Air Inter Network','nav.review':'Revisión del vuelo','nav.settings':'Ajustes',
      'display.language':'Idioma','display.style':'Estilo','display.modern':'Moderno','display.2000':'Años 2000','display.appearance':'Apariencia','display.light':'Claro','display.dark':'Noche',
      'header.direction':'DIRECCIÓN DE OPERACIONES DE VUELO','header.subtitle':'El cliente ACARS oficial de Air Inter VA.',
      'auth.crew':'Acceso de tripulación','auth.title':'Inicio de sesión del piloto','auth.hint':'Inicia sesión con tu cuenta de piloto de Air Inter para preparar y seguir tu vuelo.',
      'auth.login':'ID de piloto o correo electrónico','auth.password':'Contraseña','auth.submit':'Iniciar sesión en Air Inter'
    },
    it: {
      'nav.connect':'Accedi','nav.flight':'Le mie operazioni','nav.record':'Registrazione','nav.map':'Monitoraggio del volo',
      'nav.journal':'Registro','nav.datalink':'Datalink','nav.network':'Air Inter Network','nav.review':'Revisione del volo','nav.settings':'Impostazioni',
      'display.language':'Lingua','display.style':'Stile','display.modern':'Moderno','display.2000':'Anni 2000','display.appearance':'Aspetto','display.light':'Chiaro','display.dark':'Notte',
      'header.direction':'DIREZIONE OPERAZIONI DI VOLO','header.subtitle':'Il client ACARS ufficiale di Air Inter VA.',
      'auth.crew':'Accesso equipaggio','auth.title':'Accesso pilota','auth.hint':'Accedi con il tuo account pilota Air Inter per preparare e seguire il volo.',
      'auth.login':'ID pilota o e-mail','auth.password':'Password','auth.submit':'Accedi ad Air Inter'
    },
    ja: {
      'nav.connect':'ログイン','nav.flight':'運航','nav.record':'記録','nav.map':'フライト追跡',
      'nav.journal':'ログブック','nav.datalink':'データリンク','nav.network':'Air Inter Network','nav.review':'フライトレビュー','nav.settings':'設定',
      'display.language':'言語','display.style':'スタイル','display.modern':'モダン','display.2000':'2000年代','display.appearance':'表示','display.light':'ライト','display.dark':'ナイト',
      'header.direction':'運航部門','header.subtitle':'Air Inter VA 公式 ACARS クライアント。',
      'auth.crew':'乗務員アクセス','auth.title':'パイロットログイン','auth.hint':'Air Inter のパイロットアカウントでログインして、フライトの準備と追跡を行います。',
      'auth.login':'パイロットID またはメール','auth.password':'パスワード','auth.submit':'Air Inter にログイン'
    },
    tr: {
      'nav.connect':'Oturum aç','nav.flight':'Operasyonlarım','nav.record':'Kayıt','nav.map':'Uçuş takibi',
      'nav.journal':'Seyir defteri','nav.datalink':'Datalink','nav.network':'Air Inter Network','nav.review':'Uçuş incelemesi','nav.settings':'Ayarlar',
      'display.language':'Dil','display.style':'Stil','display.modern':'Modern','display.2000':'2000’ler','display.appearance':'Görünüm','display.light':'Açık','display.dark':'Gece',
      'header.direction':'UÇUŞ OPERASYONLARI DİREKTÖRLÜĞÜ','header.subtitle':'Air Inter VA’nın resmî ACARS istemcisi.',
      'auth.crew':'Ekip erişimi','auth.title':'Pilot girişi','auth.hint':'Uçuşunuzu hazırlamak ve takip etmek için Air Inter pilot hesabınızla oturum açın.',
      'auth.login':'Pilot kimliği veya e-posta','auth.password':'Parola','auth.submit':'Air Inter’a giriş yap'
    },
    de: {
      'nav.connect':'Anmelden','nav.flight':'Meine Operationen','nav.record':'Aufzeichnung','nav.map':'Flugverfolgung',
      'nav.journal':'Logbuch','nav.datalink':'Datalink','nav.network':'Air Inter Network','nav.review':'Flugauswertung','nav.settings':'Einstellungen',
      'display.language':'Sprache','display.style':'Stil','display.modern':'Modern','display.2000':'2000er','display.appearance':'Darstellung','display.light':'Hell','display.dark':'Nacht',
      'header.direction':'FLUGBETRIEBSLEITUNG','header.subtitle':'Der offizielle ACARS-Client von Air Inter VA.',
      'auth.crew':'Crew-Zugang','auth.title':'Pilotenanmeldung','auth.hint':'Melde dich mit deinem Air-Inter-Pilotenkonto an, um deinen Flug vorzubereiten und zu verfolgen.',
      'auth.login':'Piloten-ID oder E-Mail','auth.password':'Passwort','auth.submit':'Bei Air Inter anmelden'
    }
  };

  const supportedLanguages = Object.freeze(['fr', 'en', 'pt', 'es', 'it', 'ja', 'tr', 'de']);

  function normalize(value) {
    const raw = String(value || '').trim().toLowerCase().replace('_', '-');
    const aliases = { 'pt-br':'pt', 'pt-pt':'pt', 'es-es':'es', 'ja-jp':'ja', 'jp':'ja', 'de-de':'de', 'it-it':'it', 'tr-tr':'tr', 'en-gb':'en', 'en-us':'en', 'fr-fr':'fr' };
    const normalized = aliases[raw] || raw.split('-')[0];
    return supportedLanguages.includes(normalized) ? normalized : 'fr';
  }

  window.HermesI18n = Object.freeze({
    defaultLanguage: 'fr',
    supportedLanguages,
    normalize,
    messages: Object.freeze(messages)
  });
})();
