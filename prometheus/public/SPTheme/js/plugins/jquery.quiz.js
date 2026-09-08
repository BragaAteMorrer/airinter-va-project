/*!
 * jQuery Quiz Plugin
 * https://github.com/Reload-Lab/jQuery-quiz
 *
 * @updated Sptemeber 29, 2023
 * @version 2.1.0
 *
 * @author Domenico Gigante <domenico.gigante@reloadlab.it>
 * @copyright (c) 2023 Reload Laboratorio Multimediale <info@reloadlab.it> (httpa://www.reloadlab.it)
 * @license MIT
 */

(function($) {

	var version = '2.1.0';

	// Customizable texts according to the language
	// (buttons, labels and error messages)
	var messages = {
		start: 'Start',
		prev: 'Back',
		next: 'Forward',
		results: 'Go to results',
		restart: 'Back to the top',
		goodluck: 'For this little test you have 5 minutes to answer all the questions. There is only one right answer per question, good luck!',
		error: 'Error',
		errmsg: [
			'Please choose an answer.',
			'Some questions have not been answered. Please, back to the top to answer all questions.'
		]
	}

	// Plugin
	$.quiz = $.fn.quiz = function(options) {
		// Internal variables
		var quizArr,
			settings = {},
			intro,
			steps = 0,
			good = 0,
			first = 0,
			hashActual;

		// Visual variable
		var CONTAINER = typeof this == 'object' ? this.first() : null,
			DIV = '<div>',

			// Elements ID
			QUIZ_ID = 'quiz-container',
			BODY_ID = 'quiz-body',
			BUTTONS_ID = 'quiz-buttons',
			PROGRESS_ID = 'quiz-progress',
			MODAL_ID = 'quiz-modal',
			COOKIE_ID = 'quiz_responses',

			// Elements CSS CLASS
			FLEX_CLASS = 'quiz_flex',
			FLEXFILL_CLASS = 'quiz_flexfill',
			BTN_CLASS = 'btn btn-primary w-100 mt-5',
			PROGRESSBAR_CLASS = 'progress';

		// Default settings
		var defaults = {
			quizJson: null,
			cookieExpire: 60,
			hidePrevBtn: true,
			hideRestartBtn: true,
			fade: true,
			randomQuestions: true,
			numQuestions: null,

			// Events
			onStep: function(step, total, question) {

				// Perform some operations at question change
				if (question) {

					// console.log(question);
				}
			},
			onResults: function(good, total, questions) {

				// Perform some operations to show a message about the results
				// console.log('results');
			},

			// Templates
			questionTpl: '<div class="' + FLEX_CLASS + '">' +
				'<h4 class="mt-0 header-title border-bottom"><%this.question.__num%>. '
				// Quiz question
				+
				'<%this.question.question%>' +
				'</h4>' +
				messages.goodluck +
				'<div class="' + FLEXFILL_CLASS + '">'
				// Cycle answers
				+
				'<%for(var index in this.answers){%>' +
				'<div class="quiz_radiogroup">'
				// Quiz radio            
				+
				'<input type="radio" class="btn-check" name="question<%this.question.__id%>" id="answer-<%this.answers[index].__id%>" value="<%this.answers[index].__id%>" data-quiz-name="quizUID_<%this.question.__id%>" data-quiz-value="<%this.answers[index].__id%>" <%this.answers[index].__checked%>>' +
				'<label class="btn btn-light" for="answer-<%this.answers[index].__id%>"><%this.answers[index].answer%></label>' +
				'</div>' +
				'<%}%>'
				// end for
				+
				'</div>' +
				'</div>',


			startBtnTpl: '<button class="' + BTN_CLASS + '">'
				// Button start
				+
				'<%this.messages.start%>' +
				'</button>',
			prevBtnTpl: '<button class="' + BTN_CLASS + '">'
				// Button previous
				+
				'<%this.messages.prev%>' +
				'</button>',
			nextBtnTpl: '<button class="' + BTN_CLASS + '">'
				// Button next
				+
				'<%this.messages.next%>' +
				'</button>',
			resultBtnTpl: '<button class="' + BTN_CLASS + '">'
				// Button go to results
				+
				'<%this.messages.results%>' +
				'</button>',
			restartBtnTpl: '<button class="' + BTN_CLASS + '">'
				// Button restart
				+
				'<%this.messages.restart%>' +
				'</button>',
			modalBtnTpl: '<button class="' + BTN_CLASS + '" data-dismiss="modal">'
				// Button restart (modal)
				+
				'<%this.messages.restart%>' +
				'</button>',
			progressTpl: false
		};

		// Plugin methods
		var methods = {

			// Localization of messages
			localization: function(translation) {
				// If 'translation' is an Object...
				if (typeof translation == 'object') {

					// Change 'messages' with 'translation'
					$.extend(messages, translation);
				}
			}
		};

		// If 'options' is a string...
		if (typeof options == 'string') {

			// If 'options' is a method's name...
			if (methods[options]) {

				// Call method
				return methods[options].apply(this, Array.prototype.slice.call(arguments, 1));
			}

			// Else...
			else {

				// 'Options' must be the url to quiz json file
				options = {
					quizJson: options
				};
			}
		}

		// If 'options' is an object, but the url to quiz json file isn't set...
		if (typeof options == 'object' && !options.quizJson) {

			// Search for data-quiz-json in container tag
			var url = CONTAINER.data('quizJson');

			// If 'url' is a string...
			if (typeof url == 'string') {

				options.quizJson = url;
			}
		}

		// Merge 'options' with 'defaults'
		$.extend(settings, defaults, options);


		/*** EVENTS ***/
		// Event: Intercept hash change
		$(window).bind('hashchange', function(e) {
			// If hide previous button is true...
			if (settings.hidePrevBtn) {

				return;
			}

			// If hash is empty...
			if (window.location.hash == '' && hashActual != null) {

				// Open first question or intro
				fadeFirst();
				return;
			}

			// Step ID
			var stepid = window.location.hash.substr(1).split('=')[1];

			// If step is an integer...
			if (/^\+?(0|[1-9]\d*)$/.test(stepid)) {

				// If actual hash is disegual to step ID
				if (parseInt(hashActual) != parseInt(stepid)) {

					// Open question
					fadeQuestion(stepid - 1);
				}
			}

			// If step ID is equal to results...
			else if (stepid == 'results' && hashActual != stepid) {

				// Open results
				fadeResults();
			}
		});


		/*** FUNCTIONS ***/
		// Function: Set up the quiz
		function init() {
			try {

				// Bootstrap 4 is required
				if (!$(document).modal) {

					throw new Error('Bootstrap 4 is required');
				}

				// Set url to quiz json file
				if (!settings.quizJson) {

					throw new Error('Set url to quiz json file');
				}
			}

			// Else...
			catch (e) {

				// Get error
				console.error(e);
			}

			// Set HTML structure
			htmlStructure();

			// Get quiz json file via ajax
			$.getJSON(settings.quizJson + '?_=' + new Date().getTime())
				.done(function(data, textStatus, jqXHR) {
					// Questions
					quizArr = data[0].questions;

					// If randomize questions is true...
					if (settings.randomQuestions) {

						shuffleQuestions(quizArr);
						settings.hidePrevBtn = true;
					}

					// If num questions is integer...
					if (typeof settings.numQuestions == 'number' && settings.numQuestions < quizArr.length) {

						quizArr = quizArr.slice(0, parseInt(settings.numQuestions));
					}

					// Intro
					intro = data[0].intro || false;

					// Number of questions
					steps = quizArr.length;

					// If there is a hash...
					if (!settings.hidePrevBtn && window.location.hash) {

						// Step ID
						var stepid = window.location.hash.substr(1).split('=')[1];

						// If step is an integer...
						if (/^\+?(0|[1-9]\d*)$/.test(stepid)) {

							// Open question
							fadeQuestion(stepid - 1);
						}

						// If step is results...
						else if (stepid == 'results') {

							// Open results
							fadeResults();
						}
					}

					// Else...
					else {

						// Open first question or intro
						fadeFirst();
					}
				})
				.fail(function(jqXHR, textStatus, errorThrown) {
					// Ajax error
					console.error('Fail');
					console.error('Error ' + textStatus);
					console.error('Incoming Text ' + jqXHR.responseText);
				});
		}

		// Function: Open first question or intro
		function openFirst() {
			// If is set html...
			if (htmlFirst()) {

				// Unset actual hash
				hashChange('intro');

				// Set class to quiz body
				quizClass('step_intro');

				// Unset progress bar
				showProgress(0);
			}
		}

		// Function: Fade first question or intro
		function fadeFirst() {
			// If 'fade' is true...
			if (settings.fade) {

				// Fade out quiz body
				$('#' + BODY_ID)
					.fadeOut(300, function() {
						// Open first question or intro
						openFirst();

						// Fade in quiz body
						$('#' + BODY_ID).fadeIn(300);
					});
			}

			// Else...
			else {

				// Open first question or intro
				openFirst();
			}
		}

		// Function: Open a question
		function openQuestion(id) {
			// Get question from quiz json
			var question = getQuestion(id);

			// If 'question' is set...
			if (question) {

				// Question ID
				var id = question[0] + 1;

				// Set actual hash
				hashChange(id);

				// Insert html into quiz container
				htmlQuestion(question);

				// Set class to quiz body
				quizClass('step_' + id);

				// Set progress bar
				showProgress(id);
			}
		}

		// Function: Fade a question
		function fadeQuestion(id) {
			// If 'fade' is true...
			if (settings.fade) {

				// Fade out quiz body
				$('#' + BODY_ID)
					.fadeOut(300, function() {
						// Open question
						openQuestion(id);

						// Fade in quiz body
						$('#' + BODY_ID).fadeIn(300);
					});
			}

			// Else...
			else {

				// Open question
				openQuestion(id);
			}
		}

		// Function: Open results
		function openResults() {
			// Set actual hash
			hashChange('results');

			// Insert html into quiz container
			htmlResults();

			// Set class to quiz body
			quizClass('step_results');

			// Unset progress bar
			showProgress(steps);

			// If 'onResults' is a function...
			if (typeof settings.onResults === 'function') {

				// Call 'onResult' function
				settings.onResults.apply($('#' + QUIZ_ID), [good, steps, quizArr]);
			}
		}

		// Function: Fade results
		function fadeResults() {
			// If 'fade' is true...
			if (settings.fade) {

				// Fade out quiz body
				$('#' + BODY_ID)
					.fadeOut(300, function() {
						// Open results
						openResults();

						// Fade in quiz body
						$('#' + BODY_ID).fadeIn(300);
					});
			}

			// Else...
			else {

				// Open results
				openResults();
			}
		}

		// Function: Returns a question starting from the id
		function getQuestion(id) {
			return (quizArr[id] && [id, quizArr[id]]) || null;
		}

		// Function: Set a property of a question
		function setQuestion(id, prop, value) {
			// Set property
			quizArr[id][prop] = value;
		}

		// Function: Set response of a question
		function setResponse(id, value) {
			// Set __response
			setQuestion(id, '__response', value)
		}

		// Function: Get response of a question
		function getResponse(id) {
			// Get response
			return (quizArr[id] && quizArr[id].__response) || false;
		}

		// Function: Returns all responses from quizArr
		function getResponses() {
			var responses = {};

			// Search for question with response
			$.each(quizArr, function(i, q) {
				// If response exists...
				if (q.__response) {

					// Set responses element
					responses['quizUID_' + i] = q.__response;
				}
			});

			return responses;
		}

		// Function: Clear all responses from quizArr
		function clearResponses() {
			// Search for question with response
			$.each(quizArr, function(i, q) {
				// If response exists...
				if (q.__response) {

					// Unset response element
					delete q.__response;
				}
			});
		}

		// Function: Check for unanswered questions
		function hasErrors(id) {
			// Get responses
			var responses = JSON.parse(getCookie(COOKIE_ID)) || getResponses();

			var questionId;
			var result = true;

			// If responses doesn't exist...
			if (!responses) {

				// Set an empty cookie responses
				responses = {};
				setCookie(COOKIE_ID, JSON.stringify(responses), settings.cookieExpire);
			}

			// Search for unanswered questions
			$.each(quizArr, function(i, q) {
				questionId = 'quizUID_' + i;

				// If previous question is without answer...
				if (i < id && !responses[questionId]) {

					// Open modal
					showModal(messages.errmsg[1], true);

					result = false;
					return false;
				}

				// If current question is without answer...
				else if (i == id && !responses[questionId]) {

					// Open modal
					showModal(messages.errmsg[0], false);

					result = false;
					return false;
				}
			});

			return result;
		}

		// Function: Check if it is the last question
		function hasLast(id) {
			var lastquestion = true;

			// Search for question
			$.each(quizArr, function(i, q) {
				// If question key is greater than id...
				if (i > id) {

					lastquestion = false;
				}
			});

			return lastquestion;
		}

		// Function: Set actual hash
		function hashChange(id) {
			// If hide previous button is false...
			if (!settings.hidePrevBtn) {

				switch (id) {

					case 'intro':
						hashActual = null;
						window.location.hash = '';
						break;

					case 'results':
						hashActual = 'results';
						window.location.hash = 'step=results';
						break;

					default:
						hashActual = id;
						window.location.hash = 'step=' + id;
				}
			}
		}

		// Function: Randomize array in-place using Durstenfeld shuffle algorithm
		function shuffleQuestions(array) {
			for (var i = array.length - 1; i > 0; i--) {

				var j = Math.floor(Math.random() * (i + 1));
				var temp = array[i];
				array[i] = array[j];
				array[j] = temp;
			}
		}

		// Function: Get html of quiz structure
		function htmlStructure() {
			// Append quiz to container
			var $quiz = $(DIV).prop('id', QUIZ_ID)
				.appendTo(CONTAINER);

			// Append body area to quiz
			$(DIV).prop('id', BODY_ID)
				.appendTo($quiz);

			// Append buttons area to quiz body
			$(DIV).prop('id', BUTTONS_ID)
				.attr('role', 'group')
				.appendTo($quiz);

			// Append progress area to quiz
			$(DIV).prop('id', PROGRESS_ID)
				.appendTo($quiz);
		}

		// Function: Set class attribute of quiz area
		function quizClass(css) {
			$('#' + QUIZ_ID)
				.removeClass()
				.addClass(css);
		}

		// Function: Empty body area e buttons area
		function htmlClear() {
			$('#' + BODY_ID).empty();
			$('#' + BUTTONS_ID).empty();
		}

		// Function: Get html of first question or intro
		function htmlFirst() {
			// Empty quiz body and quiz buttons areas
			htmlClear();

			// If 'intro' is set in the json... 
			if (intro) {

				// Append start button to quiz buttons area
				$(TemplateEngine(settings.startBtnTpl, {
						'messages': messages
					}))
					.appendTo('#' + BUTTONS_ID)

					// Event: Start the quiz
					.on('click', function(e) {
						e.preventDefault();

						// Open first question
						fadeQuestion(first);
					});

				return true;
			}

			// Open first question
			fadeQuestion(first);
		}

		// Function: Get html of a question
		function htmlQuestion(q) {
			// Empty quiz body and quiz buttons areas
			htmlClear();

			var questionId = 'quizUID_' + q[0];

			// Get responses
			var responses = JSON.parse(getCookie(COOKIE_ID)) || getResponses();

			// Question object
			var question = q[1];
			question.__id = q[0];
			question.__num = q[0] + 1;

			// Answers array
			var answers = [];

			// Answers
			$.each(question.answers, function(i, r) {
				// Answer object
				var answer = r;
				answer.__id = i;
				answer.__num = i + 1;
				answer.__checked = '';

				// If an answer has already been chosen for the question...
				if (responses && responses[questionId] && responses[questionId] == i) {

					answer.__checked = ' checked';
				}

				// Push answer in answers array
				answers.push(answer);
			});

			// Append question to quiz body area
			$question = $(TemplateEngine(settings.questionTpl, {
					'question': question,
					'answers': answers
				}))
				.appendTo('#' + BODY_ID);

			try {

				var $input = $question.find('[data-quiz-name]');

				// If radio buttons exist...
				if ($input.length) {

					// Event: Record response
					$input.on('click', function(e) {
						// Get responses
						var responses = JSON.parse(getCookie(COOKIE_ID)) || getResponses();

						var name = $(this).data('quizName');
						var value = $(this).data('quizValue');

						// New response
						var obj = {};
						obj[name] = '' + value;

						// Set __response in quizArr
						setResponse(q[0], obj[name]);

						// Merge new response with other responses
						$.extend(responses, obj);

						// Set cookie responses
						setCookie(COOKIE_ID, JSON.stringify(responses), settings.cookieExpire);
					});
				}

				// Else...
				else {

					// No response set
					throw new Error('No response set!');
				}
			}

			// Else no response set...
			catch (e) {

				// Get error
				console.error(e);
			}

			// If hide previous button is false...
			if (!settings.hidePrevBtn) {

				// Append prev button to quiz buttons area
				$(TemplateEngine(settings.prevBtnTpl, {
						'question': question,
						'messages': messages
					}))
					.appendTo('#' + BUTTONS_ID)

					// Event: Move on to the prev question
					.on('click', function(e) {
						e.preventDefault();

						// Question num
						var questnum = q[0];

						// If 'onStep' is a function...
						if (typeof settings.onStep === 'function') {

							// Call 'onStep' function
							settings.onStep.apply($('#' + QUIZ_ID), [questnum, steps, quizArr[questnum]]);
						}

						// If 'question ID' is less than zero...
						if ((questnum - 1) < 0) {

							// Open first question or intro
							fadeFirst();
						}

						// Else...
						else {

							// Open previous question
							fadeQuestion(questnum - 1);
						}
					});
			}

			// If it is the last question...
			if (hasLast(q[0])) {

				// Append results button to quiz buttons area
				$(TemplateEngine(settings.resultBtnTpl, {
						'question': question,
						'messages': messages
					}))
					.appendTo('#' + BUTTONS_ID)

					// Event: Show results
					.on('click', function(e) {
						e.preventDefault();

						// Question num
						var questnum = q[0];

						// If 'onStep' is a function...
						if (typeof settings.onStep === 'function') {

							// Call 'onStep' function
							settings.onStep.apply($('#' + QUIZ_ID), [questnum, steps, quizArr[questnum]]);
						}

						// If there isn't errors...
						if (hasErrors(questnum)) {

							// Open results
							fadeResults();
						}
					});
			}

			// Else if...
			else {

				// Append next button to quiz buttons area
				$(TemplateEngine(settings.nextBtnTpl, {
						'question': question,
						'messages': messages
					}))
					.appendTo('#' + BUTTONS_ID)

					// Event: Move on to the next question
					.on('click', function(e) {
						e.preventDefault();

						// Question num
						var questnum = q[0];

						// If 'onStep' is a function...
						if (typeof settings.onStep === 'function') {

							// Call 'onStep' function
							settings.onStep.apply($('#' + QUIZ_ID), [questnum, steps, quizArr[questnum]]);
						}

						// If there isn't errors...
						if (hasErrors(questnum)) {

							// Open next question
							fadeQuestion(questnum + 1);
						}
					});
			}
		}

		// Function: Get html of results
		function htmlResults() {
			// Empty quiz body and quiz buttons areas
			htmlClear();

			// Reset good response
			good = 0;

			// Get responses
			var responses = JSON.parse(getCookie(COOKIE_ID)) || getResponses();

			// Search for question
			$.each(quizArr, function(i, q) {
				var questionId = 'quizUID_' + i;

				// Question object
				var question = q;
				question.__id = i;
				question.__num = i + 1;

				// Answers
				$.each(q.answers, function(i, r) {
					// If an answer has already been chosen for the question...
					if (responses && responses[questionId] && responses[questionId] == i) {

						// Answer object
						answer = r;
						answer.__id = i;
						answer.__num = i + 1;

						// If the answer is right...
						if (r.true == 1) {

							// Correct answers
							good++;
						}
					}
				});

			});

			// If hide restart button is false...
			if (!settings.hideRestartBtn) {

				// Append restart button to quiz buttons area
				$(TemplateEngine(settings.restartBtnTpl, {
						'messages': messages
					}))
					.appendTo('#' + BUTTONS_ID)

					// Event: Start over from the beginning
					.on('click', function(e) {
						e.preventDefault();

						// Clear all __response in quizArr
						clearResponses();

						// Erase cookie responses
						eraseCookie(COOKIE_ID);

						// If randomize questions is true...
						if (settings.randomQuestions) {

							shuffleQuestions(quizArr);
						}

						// Open first question or intro
						fadeFirst();
					});
			}
		}

		// Function: Set progress bar, if it doesn't exists, and show/hide it
		function showProgress(id) {
			// Width of bar
			var perc = (id / steps) * 100;

			// If 'progressTpl' is set...
			if (typeof settings.progressTpl == 'string') {

				// Progress object
				var progress = {
					'step': id,
					'total': steps,
					'percent': perc
				}

				// Append progress bar to quiz progress area
				$('#' + PROGRESS_ID)
					.html(TemplateEngine(settings.progressTpl, {
						'progress': progress
					}));
			}

			// Else...
			else {
				// Suche nach dem Fortschrittsbereich
				var $bar = $('#' + PROGRESS_ID).find('.' + PROGRESSBAR_CLASS);

				// Wenn die Fortschrittsleiste noch nicht existiert
				if (!$bar.length) {
					// Fortschrittsleiste hinzufügen
					$bar = $(`
        <div class="${PROGRESSBAR_CLASS} mt-3" role="progressbar" aria-label="warning example" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
            <span class="progress-bar bg-warning" style="width: 0%"></span>
        </div>
    `).appendTo('#' + PROGRESS_ID);
				}

				// Breite und Text aktualisieren
				$bar.find('.progress-bar')
					.css('width', perc + '%') // Setze die Breite auf `perc`
					.text(id + '/' + steps); // Aktualisiere den Text innerhalb des <span>

				// Aktualisiere das `aria-valuenow`-Attribut des äußeren Containers
				$bar.attr('aria-valuenow', perc);

			}
		}

		// Function: Set bootstrap modal, if it doesn't exists, and show it
		function showModal(error, back) {
			// Suche nach der Modal-Instanz
			var modalId = MODAL_ID;
			var $mdl = document.getElementById(modalId);

			// Falls die Modal-Struktur nicht existiert
			if (!$mdl) {
				var html = `

            <div class="modal fade" id="${modalId}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="${modalId}Label" aria-hidden="true">
               <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                     <div class="modal-header pb-0">
                        <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-warning-circle fs-20 me-2"></i>${messages.error}</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i class="ph-fill ph-minus text-danger fs-20"></i></button>
                     </div>
                     <div class="modal-body pt-0"></div>
                  </div>
               </div>
            </div>`;
				document.body.insertAdjacentHTML("beforeend", html);

				// Falls der Neustart-Button angezeigt werden soll
				if (!settings.hideRestartBtn) {
					const restartButton = document.createElement("button");
					restartButton.className = BTN_CLASS;
					restartButton.innerHTML = messages.restart;
					restartButton.setAttribute("data-bs-dismiss", "modal");
					restartButton.onclick = function(e) {
						e.preventDefault();
						clearResponses();
						eraseCookie(COOKIE_ID);
						fadeFirst();
					};
				}

				// Warte kurz und öffne dann das Modal
				setTimeout(() => showModal(error, back), 10);
			} else {
				// Fehlernachricht und Buttons setzen, dann das Modal öffnen
				$mdl.querySelector(".modal-body").textContent = error;

				// Modal mit Bootstrap 5 API anzeigen
				var modalInstance = new bootstrap.Modal($mdl);
				modalInstance.show();
			}
		}



		/*** COOKIE ***/
		// Set a cookie
		function setCookie(name, value, seconds) {
			var expires = '';

			// If seconds is set...
			if (seconds) {

				// Set cookie expiring date
				var date = new Date();
				date.setTime(date.getTime() + (seconds * 1000));
				expires = '; expires=' + date.toUTCString();
			}

			document.cookie = name + '=' + (value || '') + expires + '; path=/';
		}

		// Get a cookie
		function getCookie(name) {
			var nameEQ = name + '=';
			var ca = document.cookie.split(';');

			for (var i = 0; i < ca.length; i++) {

				var c = ca[i];

				while (c.charAt(0) == ' ') {

					c = c.substring(1, c.length);
				}

				if (c.indexOf(nameEQ) == 0) {

					return c.substring(nameEQ.length, c.length);
				}
			}

			return null;
		}

		// Delete a cookie
		function eraseCookie(name) {
			document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
		}


		/*** TEMPLATE ENGINE ***/
		// JavaScript template engine by Krasimir Tsonev
		// https://krasimirtsonev.com/blog/article/Javascript-template-engine-in-just-20-line
		function TemplateEngine(html, options) {
			var re = /<%(.+?)%>/g,
				reExp = /(^( )?(var|if|for|else|switch|case|break|{|}|;))(.*)?/g,
				code = 'with(obj) { var r=[];\n',
				cursor = 0,
				result,
				match;

			function add(line, js) {
				js ? (code += line.match(reExp) ?
						line + '\n' : 'r.push(' + line + ');\n') :
					(code += line != '' ? 'r.push("' + line.replace(/"/g, '\\"') + '");\n' : '');

				return add;
			}

			while (match = re.exec(html)) {

				add(html.slice(cursor, match.index))(match[1], true);
				cursor = match.index + match[0].length;
			}

			add(html.substr(cursor, html.length - cursor));

			code = (code + 'return r.join(""); }').replace(/[\r\t\n]/g, ' ');

			try {

				result = new Function('obj', code).apply(options, [options]);
			} catch (err) {

				console.error("'" + err.message + "'", " in \n\nCode:\n", code, "\n");
			}

			return result;
		}


		/*** INIT ***/
		init();

		return this;
	}
}(jQuery));