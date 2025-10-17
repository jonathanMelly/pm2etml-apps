import { PDFDocument, rgb } from 'pdf-lib';

const state = window.evaluationState;

document.addEventListener('DOMContentLoaded', function () {

   console.log('les val de state ; ', state);

   // Gestion des boutons de soumission
   const submitBtns = document.querySelectorAll('[id^="id-"][id$="-buttonSubmit"]');

   // Sélectionner tous les boutons de soumission
   addSubmitButtonListeners(submitBtns);

   handleTabSwitch();

   if (state.jsonSave && typeof loadFrom === 'function') {
      state.jsonSave.forEach(js => {
         if (js.evaluations) {
            loadFrom(js); // Passe l'objet actuel à loadFrom
         } else {
            console.log(`Aucune évaluation trouvée pour l'étudiant ${js.id}`);
         }
      });
   } else {
      console.error('jsonSave or loadFrom is not defined');
   }

   // Initialisation des sliders
   const sliders = document.querySelectorAll('.range');
   sliders.forEach(slider => { syncSliders(slider); });
   // Active le ranger selon le btn 
   enableRanges();

   // Intégration de la fonction updateTextareaGeneralRemark
   window.updateTextareaGeneralRemark = function (textarea, counter) {
      const maxLength = 10000;
      const currentLength = textarea.value.length;
      const remainingCharacters = maxLength - currentLength;
      counter.textContent = remainingCharacters + '/' + maxLength;
   };

   // Ajouter l'écouteur d'événement à chaque textarea
   const textareas = document.querySelectorAll('.remark textarea');
   textareas.forEach(textarea => {
      const counter = textarea.closest('.remark').querySelector('#charCounter');  // Récupère le compteur associé
      if (counter) {
         // Met à jour le compteur initialement
         window.updateTextareaGeneralRemark(textarea, counter);

         // Met à jour le compteur chaque fois que l'utilisateur tape dans le textarea
         textarea.addEventListener('input', function () {
            window.updateTextareaGeneralRemark(textarea, counter);
         });
      }
   });


   // En cours de dev. pour version 2
   // if (state.isTeacher) {
   //    // Cibler les zones de texte avec un clic droit
   //    document.querySelectorAll('.remark textarea').forEach((textarea) => {
   //       textarea.addEventListener('contextmenu', (event) => {
   //          event.preventDefault(); // Empêche le menu contextuel par défaut

   //          // Basculer en mode "to-do"
   //          const container = textarea.closest('.remark');
   //          const todoListContainer = container.querySelector('#todo-list-container');

   //          textarea.classList.add('hidden'); // Cache la zone de texte
   //          todoListContainer.classList.remove('hidden'); // Affiche la liste de tâches

   //          // Ajouter les classes Tailwind nécessaires pour Flexbox 
   //          todoListContainer.classList.add('flex', 'gap-5', 'flex-wrap');
   //       });
   //    });

   // Appliquer les messages workflow dès le chargement (si data-workflow présent)
   document.querySelectorAll('.evaluation-tabs').forEach((container) => {
      applyWorkflowMessages(container); toggleValidateButton(container);
   });

});

window.enableRanges = function () {
   // On récupère le conteneur des étudiants visibles
   const containerStudentsVisible = document.querySelector('#ContainerStudentsVisible');

   if (!containerStudentsVisible) return;

   // On cherche l'élément visible (style != "none")
   const visibleStudent = [...containerStudentsVisible.children].find(sv => sv.style.display !== "none");

   if (!visibleStudent) return;

   // On récupère l'ID de l'étudiant visible
   const idStudentVisible = visibleStudent.id.split('-')[1];

   // On récupère la zone des boutons
   const zoneBtns = document.querySelector(`#id-${idStudentVisible}-btn`);

   if (!zoneBtns) return;

   // Récupération des valeurs des boutons sous forme d'un objet clé-valeur
   const whichBtns = {
      [`#id-${idStudentVisible}-btn-auto80`]: parseInt(zoneBtns.dataset.btnauto80ison, 10),
      [`#id-${idStudentVisible}-btn-eval80`]: parseInt(zoneBtns.dataset.btneval80ison, 10),
      [`#id-${idStudentVisible}-btn-auto100`]: parseInt(zoneBtns.dataset.btnauto100ison, 10),
      [`#id-${idStudentVisible}-btn-eval100`]: parseInt(zoneBtns.dataset.btneval100ison, 10)
   };
   // Trouver la première clé où la valeur est 1
   const thisBtn = Object.keys(whichBtns).find(key => whichBtns[key] === 1);

   if (!thisBtn) return; // Aucun bouton activé

   const btn = document.querySelector(thisBtn);

   // Appeler la fonction changeTab avec la bonne valeur
   changeTab(btn);
};


window.editEvaluation = function (studentId) {
   notify("✋ Cette option n’est pas encore disponible.", "info");
}

window.handleTabSwitch = function () {
   const tabs = document.querySelectorAll('[role="tab"]');

   tabs.forEach((tab, index) => {
      tab.addEventListener('click', function () {
         // Désactiver tous les onglets
         tabs.forEach(t => {
            t.setAttribute('aria-selected', 'false');
            t.classList.remove('bg-gray-200', 'text-black', 'border-gray-800', 'shadow-md');
            t.classList.add('bg-gray-300', 'text-gray-700', 'hover:bg-gray-400');
         });

         // Activer l'onglet cliqué
         tab.setAttribute('aria-selected', 'true');
         tab.classList.remove('bg-gray-300', 'text-gray-700', 'hover:bg-gray-400');
         tab.classList.add('bg-gray-200', 'text-black', 'border-gray-800', 'shadow-md');

         // Ici, vous pouvez utiliser les indices pour gérer la visibilité du contenu des onglets
         toggleVisibilityStudentContainer(`#idStudent-${tab.id.split('-')[1]}-visible`);
      });
   });
}

function toggleVisibilityStudentContainer(student_id_visibility) {
   const allContainers = document.querySelector('#ContainerStudentsVisible');
   const newVisibleContainer = document.querySelector(student_id_visibility);

   // Masquer tous les conteneurs
   Array.from(allContainers.children).forEach(child => {
      if (child.style.display !== 'none') {
         child.style.display = 'none';
      }
   });

   // Afficher le conteneur sélectionné
   if (newVisibleContainer) {
      newVisibleContainer.style.display = 'block';
   }
}

// Fonction globale pour gérer l'affichage/masquage du textarea
window.toggleRemark = function (label) {
   // Récupérer l'input checkbox associé à ce label
   const checkbox = label.querySelector('input.swap-input');
   if (!checkbox) return; // Vérifie si le checkbox existe

   // Récupérer l'ID du criterion depuis les données du checkbox
   const remarkId = checkbox.getAttribute('data-remark-id');
   if (!remarkId) return; // Vérifie si l'ID est valide

   // Trouver le textarea correspondant
   const textarea = document.querySelector(`textarea[data-textarea-id="${remarkId}"]`);
   if (!textarea) return; // Vérifie si le textarea existe

   // Vérifier si le checkbox est coché
   if (checkbox.checked) {
      // Si coché, afficher le textarea
      textarea.classList.remove('hidden');
   } else {
      // Sinon, masquer le textarea
      textarea.classList.add('hidden');
   }
};


window.toggleCheckbox = function (label) {
   // Récupérer l'input checkbox associé à ce label
   const checkbox = label.querySelector('input.swap-input');
   if (!checkbox) return; // Vérifie si le checkbox existe

   const studentId = checkbox.getAttribute('data-student-id');
   if (!studentId) {
      console.warn("Aucun attribut 'data-student-id' trouvé sur le checkbox.");
      return;
   }

   // Récupérer tous les boutons liés à cet étudiant
   const btns = document.querySelectorAll(`#id-${studentId}-btn > button`);
   if (btns.length === 0) {
      console.warn(`Aucun bouton trouvé pour l'étudiant ${studentId}`);
      return;
   }

   // Trouver le bouton actif (celui qui n'a pas la classe 'btn-outline')
   const btnActif = Array.from(btns).find(btn => !btn.classList.contains('btn-outline'));

   if (!btnActif) {
      console.warn(`Aucun bouton actif trouvé pour l'étudiant ${studentId}`);
      return;
   }

   // Récupérer le nom du niveau depuis l'attribut 'data-level' du bouton actif
   const levelName = btnActif.getAttribute('data-level');
   if (!levelName) {
      console.warn(`L'attribut 'data-level' est manquant sur le bouton actif pour l'étudiant ${studentId}`);
      return;
   }

   // Appeler la fonction pour recalculer les résultats finaux
   calculateFinalResults(studentId, levelName);
};

/**
 * Met à jour le label associé au range (slider).
 *
 * Cette fonction prend un élément slider en paramètre, extrait l'ID du label associé,
 * et met à jour le contenu textuel du label en fonction de la valeur du slider.
 * Si le label n'est pas trouvé, un message d'erreur est affiché dans la console.
 *
 * @param {HTMLElement} slider - L'élément slider dont la valeur a changé.
 */
function syncSliders(slider) {
   // Extrait l'ID du label associé en remplaçant '-range' par '-result' dans l'ID du slider.
   const id = slider.id.replace('-range', '-result');

   // Récupère l'élément label associé en utilisant l'ID généré.
   const resultLabel = document.getElementById(id);

   // Vérifie si le label existe.
   if (resultLabel) {
      // Met à jour le contenu textuel du label avec la valeur correspondante dans state.appreciationLabels.
      resultLabel.textContent = state.appreciationLabels[slider.value];
   } else {
      // Affiche un message d'erreur dans la console si le label n'est pas trouvé.
      console.error('Erreur : Label résultat non trouvé', id);
   }
}

function getEvaluationLevelIndex(levelName) {
   const keys = Object.keys(state.evaluationLabels);
   const index = keys.indexOf(levelName);
   console.log("getEval dynamic:", levelName, "=> index:", index, keys);
   return index !== -1 ? index : 0;
}


function getGeneralRemark(studentId) {
   const idGeneralRemark = `id-${studentId}-generalRemark`;
   const generalRemark = document.getElementById(idGeneralRemark).value.trim();
   return generalRemark;
}

/**
 * Affiche un message d'erreur dans le formulaire pour un étudiant donné.
 *
 * @param {number|string} studentId - L'ID de l'étudiant associé à l'élément d'erreur.
 * @param {string} message - Le message d'erreur à afficher.
 */
function displayError(studentId, message) {
   // Vérifications initiales
   if (!studentId || typeof studentId !== 'number' && typeof studentId !== 'string') {
      console.error('❌ Erreur : L\'ID de l\'étudiant doit être un nombre ou une chaîne de caractères.');
      return;
   }

   if (typeof message !== 'string' || message.trim() === '') {
      console.error('❌ Erreur : Le message d\'erreur ne peut pas être vide ou invalide.');
      return;
   }

   // Sélectionner le conteneur d'erreur correspondant
   const errorDiv = document.getElementById(`errors-${studentId}`);

   if (!errorDiv) {
      console.warn(`⚠️ Avertissement : Aucun conteneur d'erreur trouvé pour l'étudiant ID: ${studentId}`);
      return;
   }

   // Mettre à jour le contenu du conteneur d'erreur
   errorDiv.textContent = message;

   // Ajouter une animation fluide pour afficher le message
   errorDiv.style.transition = 'opacity 0.3s ease-in-out, max-height 0.3s ease-in-out';
   errorDiv.style.opacity = '1';
   errorDiv.style.maxHeight = `${errorDiv.scrollHeight + 25}px`; // Ajuster la hauteur pour éviter les saccades

   // Retirer la classe "hidden" pour rendre le conteneur visible
   errorDiv.classList.remove('hidden');

   // Optionnel : Masquer automatiquement le message après 5 secondes
   setTimeout(() => hideError(errorDiv), 5000);
}

/**
 * Masque un message d'erreur avec une animation fluide.
 *
 * @param {HTMLElement} errorDiv - Le conteneur d'erreur à masquer.
 */
function hideError(errorDiv) {
   if (!errorDiv) return;

   errorDiv.style.transition = 'opacity 0.3s ease-in-out, max-height 0.3s ease-in-out';
   errorDiv.style.opacity = '0';
   errorDiv.style.maxHeight = '0';

   // Après l'animation, réappliquer la classe "hidden"
   setTimeout(() => {
      errorDiv.classList.add('hidden');
      errorDiv.style.maxHeight = null; // Réinitialiser la hauteur
   }, 300); // Durée de l'animation (0.3s)
}


window.updateSliderValue = function (slider) {
   // Synchronise le label du curseur (affiche NA, PA, A, LA)
   const id = slider.id.replace('-range', '-result');
   syncSliders(slider);

   // Extraction de l'ID, du niveau et de l'index du critère
   const match = slider.id.match(/^id-(\d+)-range-([^-]+)-(\d+)$/);
   if (!match) {
      console.error("Format d'ID de slider invalide :", slider.id);
      return;
   }

   const [_, studentId, level, criterionIndex] = match;
   const value = parseInt(slider.value);

   // Recalcul dynamique des résultats
   calculateFinalResults(studentId, level);

   // Récupération de la zone de remarque associée
   const remarkId = `id-${studentId}-remark-${criterionIndex}`;
   const remarkField = document.querySelector(`textarea[data-student-id="${studentId}"][data-textarea-id="${criterionIndex}"]`) || document.getElementById(remarkId);

   if (!remarkField) {
      console.warn(`Champ de remarque non trouvé pour : ${remarkId}`);
      return;
   }

   // Si la valeur est basse (NA ou PA), on exige une remarque
   const requireRemark = value < 2;
   remarkField.classList.toggle('border-red-500', requireRemark);
   remarkField.toggleAttribute('required', requireRemark);

   if (requireRemark) {
      window.openRemark(studentId, criterionIndex);

      // Affiche un petit message pédagogique dans la console
      const shortLabel = window.evaluationState?.evaluationShortLabels?.[level] || level;
      console.info(`(${shortLabel}) - Niveau faible détecté → remarque obligatoire`);
   }
};


window.openRemark = function (studentId, criterionIndex) {
   const textarea = document.querySelector(`textarea[data-student-id="${studentId}"][data-textarea-id="${criterionIndex}"]`);
   if (textarea) {
      textarea.classList.remove('hidden');

      // Ajout d'une bordure explicite rouge
      textarea.style.border = '1px solid red !important';
      textarea.style.boxShadow = '0 0 3px 1px rgba(255, 0, 0, 0.6)'; // Ajoute un effet de halo pour renforcer la visibilité

      // Fait défiler la page pour amener le champ dans la vue si besoin
      textarea.scrollIntoView({ behavior: 'smooth', block: 'center' });

      // Retirer l'effet après 1 seconde
      setTimeout(() => {
         textarea.style.border = ''; // Réinitialise la bordure
         textarea.style.boxShadow = ''; // Réinitialise le halo
      }, 600);
   } else {
      console.warn(`Textarea non trouvé pour studentId=${studentId}, criterionIndex=${criterionIndex}`);
   }
};

// Fonction qui permet de changer l'onglet (autoFormative, evalFormative, etc.)
window.changeTab = function (onClickBtn) {
   const studentId = onClickBtn.dataset.studentId || onClickBtn.closest('.evaluation-tabs')?.id.split('-')[1];
   const selectedLevel = onClickBtn.dataset.level;
   const isTeacher = onClickBtn.closest('.evaluation-tabs')?.dataset.role === 'teacher';
   const buttonClass = isTeacher ? 'btn-secondary' : 'btn-primary';

   // Supprime le message d’aide si présent
   const helpMsg = document.getElementById(`help-msg-${studentId}`);
   if (helpMsg) helpMsg.remove();

   // Réinitialise les boutons
   const allBtns = document.querySelectorAll(`#id-${studentId}-btn > button.eval-tab-btn`);
   allBtns.forEach(btn => {
      btn.classList.add('btn-outline');
      btn.classList.remove(buttonClass);
   });
   onClickBtn.classList.remove('btn-outline');
   onClickBtn.classList.add(buttonClass);

   // Désactive tous les curseurs (ne masque pas les lignes existantes)
   const allRanges = document.querySelectorAll(`input[type="range"][data-student-id="${studentId}"]`);
   allRanges.forEach(r => (r.disabled = true));

   // Active uniquement les curseurs du niveau choisi + effet visuel
   // Si enseignant et passage à ENS‑S: ELEV‑F2 est optionnelle → informer mais autoriser
   if (isTeacher && selectedLevel === 'eSommative' && !hasStudentAFormative2(studentId)) {
      notify("ELEV‑F2 optionnelle non faite.", 'info');
      // on n'interdit pas: on continue pour permettre l'évaluation ENS‑S
   }

   const activeRanges = document.querySelectorAll(`input[data-student-id="${studentId}"][data-level="${selectedLevel}"]`);
   activeRanges.forEach(r => {
      // Afficher la ligne correspondante si elle était masquée (ex. ELEV‑S / ENS‑S)
      const row = r.closest(`div[id^="id-${studentId}-${selectedLevel}-"]`);
      if (row) row.style.display = 'flex';

      r.disabled = false;
      r.classList.add('ring-1', 'ring-amber-500', 'ring-offset-1'); // halo visuel
      setTimeout(() => r.classList.remove('ring-1', 'ring-amber-500', 'ring-offset-1'), 800);
   });

   // Mise à jour de la logique métier (si tu as déjà cette fonction)
   if (typeof calculateFinalResults === 'function') {
      calculateFinalResults(studentId, selectedLevel);
   }
};


// Fonction pour afficher un message popup
function showReloadPopup(message, delay = 2000) {
   // Création d'une boîte de dialogue personnalisée
   const popup = document.createElement("div");
   popup.style.position = "fixed";
   popup.style.top = "50%";
   popup.style.left = "50%";
   popup.style.transform = "translate(-50%, -50%)";
   popup.style.backgroundColor = "#333";
   popup.style.color = "#fff";
   popup.style.padding = "15px 25px";
   popup.style.borderRadius = "5px";
   popup.style.zIndex = "9999";
   popup.style.fontSize = "16px";
   popup.textContent = message;

   // Ajout du popup au DOM
   document.body.appendChild(popup);

   // Suppression du popup après un certain délai
   setTimeout(() => {
      document.body.removeChild(popup);
      location.reload(); // Rechargement de la page après suppression du popup
   }, delay);
}

window.validateEvaluation = async function (idStudent) {
   const idStudentVisible = `#idStudent-${idStudent}-visible`;
   const idEval = document.querySelector(idStudentVisible)?.querySelector(`[id^="id_eval-"]`);
   const idStudentBtn = `#id-${idStudent}-btn`;
   const btns = document.querySelector(idStudentBtn);
   const btn = OnThisRangesFotIdStudent(idStudent);

   // Appel des fonctions nécessaires
   OnThisBtn(btn);
   try {
      const resp = await setStatusEvalInBD(idEval, btns);
      if (resp && resp.success) {
         // Informer seulement; mettre à jour le workflow exposé pour l'UI
         if (resp.workflow) {
            btns.setAttribute('data-workflow', resp.workflow);
            applyWorkflowMessages(btns);
            try { toggleValidateButton(btns); } catch (_) {}
         }
         await notifyStatusEval(btns);
      } else {
         notify(resp?.message || "La transition n'a pas pu être appliquée.", 'error');
      }
   } catch (e) {
      console.error(e);
      notify("Erreur pendant la validation.", 'error');
   }
};


/**
 * Affiche une notification flottante à l'écran.
 *
 * @param {string} message - Le message à afficher.
 * @param {string} [type='success'] - Le type de notification ('success', 'error', 'info', 'warning').
 */
function notify(message, type = 'success') {
   if (!message || typeof message !== 'string') {
      console.error('Message de notification invalide.');
      return;
   }

   // Vérifie si un popup identique existe déjà
   const existingPopup = document.querySelector('.popup-notification');
   if (existingPopup) {
      existingPopup.remove();
   }

   // Couleurs selon le type
   const typeColors = {
      success: '#34c759',
      error: '#ff3b30',
      info: '#007aff',
      warning: '#ffcc00'
   };

   const backgroundColor = typeColors[type] || typeColors.success;

   // Créer le popup
   const popup = document.createElement('div');
   popup.className = 'popup-notification';
   popup.style.cssText = `
      position: fixed;
      bottom: 20px;
      right: 20px;
      background-color: ${backgroundColor};
      color: white;
      padding: 15px 20px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      z-index: 9999;
      display: flex;
      align-items: center;
      justify-content: space-between;
      min-width: 250px;
      max-width: 350px;
      font-family: sans-serif;
      animation: fadeIn 0.3s ease-out;
   `;

   // Contenu
   popup.innerHTML = `
      <span style="flex: 1;">${message}</span>
      <button class="popup-close" style="background: none; border: none; color: white; font-size: 20px; margin-left: 15px; cursor: pointer;">×</button>
   `;

   // Ajout au DOM
   document.body.appendChild(popup);

   // Fermeture manuelle
   popup.querySelector('.popup-close').addEventListener('click', () => removePopup(popup));

   // Fermeture automatique
   setTimeout(() => removePopup(popup), 5000);
}

// Optionnel : animation CSS (tu peux l'ajouter dans ton CSS global)
const style = document.createElement('style');
style.textContent = `
@keyframes fadeIn {
   from { opacity: 0; transform: translateY(10px); }
   to { opacity: 1; transform: translateY(0); }
}
@keyframes fadeOut {
   from { opacity: 1; transform: translateY(0); }
   to { opacity: 0; transform: translateY(10px); }
}
`;
document.head.appendChild(style);


function notifyStatusEval(btns) {
   if (!btns) {
      console.error("ID d'évaluation non trouvé.");
      return;
   }
// Affichage du bouton Valider/Terminer selon le workflow
function toggleValidateButton(btns) {
   const role = btns.getAttribute('data-role') || '';
   const wf = btns.getAttribute('data-workflow') || '';
   const validateBtn = btns.querySelector('button.btn-success');
   if (!validateBtn) return;

   let show = false;
   if (role === 'student') {
      show = (wf === 'teacher_summative_done');
   } else if (role === 'teacher') {
      show = (wf === 'waiting_teacher_validation_f' || wf === 'waiting_teacher_validation_f2' || wf === 'waiting_teacher_summative' || wf === 'teacher_summative_done' || wf === 'summative_validated');
      validateBtn.textContent = (wf === 'teacher_summative_done' || wf === 'summative_validated') ? 'Terminer' : 'Valider';
   }
   validateBtn.style.display = show ? '' : 'none';
}

   const role = btns.getAttribute('data-role') || '';
   const workflow = btns.getAttribute('data-workflow') || '';
   const currentState = btns.getAttribute('data-current-state') || 'not_started';

   // Messages concis basés sur le workflow (prioritaire)
   const wfMessages = {
      waiting_student_formative: "En attente d'auto-éval élève (ELEV-F).",
      waiting_student_formative2_optional: "Vous pouvez faire ELEV‑F2 (optionnel).",
      waiting_teacher_validation_f: "À valider par l'enseignant (ENS-F).",
      teacher_ack_formative: "Accusé de réception de l'enseignant (F).",
      teacher_formative_done: "Évaluation formative enseignant effectuée.",
      waiting_student_validation_f: "Validation élève (formative) requise.",
      formative_validated: "Phase formative clôturée.",
      waiting_teacher_summative: "À évaluer par l'enseignant (ENS-S).",
      teacher_summative_done: "Évaluation sommative enseignant effectuée.",
      summative_validated: "Validation élève enregistrée.",
      closed_by_teacher: "Évaluation clôturée.",
   };

   // Fallback sur l'état principal (timing)
   const stateMessages = {
      not_started: "Débutez l'évaluation.",
      autoFormative: "Auto-évaluation formative enregistrée.",
      evalFormative: "Formative validée.",
      autoFinale: "Auto-évaluation F2 enregistrée.",
      evalFinale: "Sommative validée.",
      pending_signature: "En attente de signature.",
      completed: "Évaluation terminée.",
   };

   const message = wfMessages[workflow] || stateMessages[currentState] || null;
   if (message) {
      notify(message, 'success');
   }
}

function applyWorkflowMessages(btns) {
   if (!btns) return;
   const role = btns.getAttribute('data-role') || '';
   const studentId = (btns.id || '').split('-')[1];
   const workflow = btns.getAttribute('data-workflow') || '';

   // Status + hint concis
   const texts = {
      waiting_student_formative: {
         status: "En attente d'auto-éval élève (ELEV‑F1).",
         hintStudent: "Cliquez sur ELEV‑F1 pour commencer.",
         hintTeacher: "En attente que l'élève commence.",
      },
      waiting_student_formative2_optional: {
         status: "Formative 2 (ELEV‑F2) optionnelle.",
         hintStudent: "Vous pouvez faire ELEV‑F2 (optionnel).",
         hintTeacher: "Invitez l'élève à ELEV‑F2 (optionnel).",
      },
      waiting_teacher_validation_f: {
         status: "À valider par l'enseignant (ENS-F).",
         hintStudent: "En attente de validation de l'enseignant.",
         hintTeacher: "Validez l'évaluation formative (ENS‑F).",
      },
      teacher_ack_formative: {
         status: "Accusé de réception (formative).",
         hintStudent: "Attendez la suite de l'enseignant.",
         hintTeacher: "Préparez votre évaluation formative.",
      },
      teacher_formative_done: {
         status: "Formative enseignant effectuée.",
         hintStudent: "Poursuivez vers F2 (si demandé).",
         hintTeacher: "Invitez l'élève à ELEV‑F2 (optionnel).",
      },
      waiting_teacher_summative: {
         status: "À évaluer (ENS-S).",
         hintStudent: "En attente de l'enseignant.",
         hintTeacher: "Réalisez l'évaluation sommative.",
      },
      teacher_summative_done: {
         status: "Sommative enseignant effectuée.",
         hintStudent: "Cliquez sur Valider pour confirmer.",
         hintTeacher: "Vous pouvez cliquer sur Terminer.",
      },
      summative_validated: {
         status: "Validation élève enregistrée.",
         hintStudent: null,
         hintTeacher: "Cliquez sur Terminer pour clôturer.",
      },
      closed_by_teacher: {
         status: "Évaluation clôturée.",
         hintStudent: null,
         hintTeacher: null,
      },
   };

   const t = texts[workflow];
   if (!t) return;

   // Mettre à jour le statut visible
   const statusSpan = btns.querySelector('.next-state-message');
   if (statusSpan) {
      statusSpan.textContent = `Statut : ${t.status}`;
   }

   // Mettre à jour ou créer le hint
   const hintId = `help-msg-${studentId}`;
   let hint = document.getElementById(hintId);
   const hintText = role === 'teacher' ? t.hintTeacher : t.hintStudent;
   if (hintText) {
      if (!hint) {
         hint = document.createElement('div');
         hint.id = hintId;
         hint.className = 'absolute -top-10 -right-3 bg-amber-50 text-amber-800 text-sm font-medium border border-amber-300 px-3 py-1 rounded-md animate-pulse';
         btns.appendChild(hint);
      }
      hint.textContent = hintText;
   } else if (hint) {
      hint.remove();
   }

   // Surligner le prochain bouton pertinent (sans forcer ELEV‑S côté élève)
   btns.querySelectorAll('button.eval-tab-btn').forEach(b => b.classList.remove('ring-2', 'ring-amber-500', 'animate-pulse'));
   const highlight = (level) => {
      const target = btns.querySelector(`button.eval-tab-btn[data-level='${level}']`);
      if (target) target.classList.add('ring-2', 'ring-amber-500', 'animate-pulse');
   };
   if (role === 'teacher') {
      if (workflow === 'waiting_teacher_validation_f' || workflow === 'teacher_ack_formative') highlight('eFormative1'); if (workflow === 'waiting_teacher_validation_f2') { const validateBtn = btns.querySelector('button.btn-success'); if (validateBtn) validateBtn.classList.add('ring-2', 'ring-amber-500', 'animate-pulse'); }
      else if (workflow === 'waiting_teacher_summative') highlight('eSommative');
      else if (workflow === 'teacher_summative_done') {
         const endBtn = btns.querySelector('button.btn-success');
         if (endBtn) endBtn.classList.add('ring-2', 'ring-emerald-500', 'animate-pulse');
      }
   } else if (role === 'student') {
      if (workflow === 'waiting_student_formative') highlight('aFormative1');
      if (workflow === 'teacher_summative_done') {
         const validateBtn = btns.querySelector('button.btn-success');
         if (validateBtn) validateBtn.classList.add('ring-2', 'ring-emerald-500', 'animate-pulse');
      }
   }

   // Ajuster la visibilité/contenu du bouton Valider/Terminer selon workflow
   try { toggleValidateButton(btns); } catch (_) {}
}


// Fonction pour supprimer le popup
function removePopup(popup) {
   if (popup && popup.parentNode) {
      popup.parentNode.removeChild(popup);
   }
}

function OnThisRangesFotIdStudent(idStudent) {
   const tabs = document.querySelector(`#id-${idStudent}-btn`);
   if (!tabs) return null;

   const role = tabs.dataset.role; // 'teacher' | 'student'
   const current = tabs.getAttribute('data-current-state') || 'not_started';

   let targetLevel;
   if (role === 'teacher') {
      targetLevel = (current === 'autoFormative' || current === 'evalFormative') ? 'eFormative1' : 'eSommative';
   } else {
      targetLevel = (current === 'not_started' || current === 'autoFormative') ? 'aFormative1' : 'aFormative2';
   }

   const btn = tabs.querySelector(`button[data-level='${targetLevel}']`);

   const divSliders = document.querySelectorAll(`[id^="id-${idStudent}-${targetLevel}-"]`);
   divSliders.forEach(div => {
      div.style.display = 'flex';
      const range = div.querySelector('input[type="range"]');
      if (range) {
         range.disabled = false;
      }
   });

   return btn;
}


function setStatusEvalInBD(idEval, btns) {
   // Vérifier si l'élément d'évaluation existe
   if (!idEval) {
      console.error("Élément d'évaluation non trouvé.");
      return;
   }

   // Récupérer l'ID de l'évaluation depuis l'attribut ID
   const evalId = idEval.id.replace('id_eval-', '');

   // Récupérer le nouvel état depuis l'attribut data-current-state
   const newState = btns.getAttribute('data-current-state');

   if (!evalId || !newState) {
      console.error("Données manquantes pour mettre à jour l'état de l'évaluation.");
      return;
   }

   // Envoyer une requête AJAX pour mettre à jour l'état dans la base de données
   return fetch('/api/evaluations/update-status', {
      method: 'POST',
      headers: {
         'Content-Type': 'application/json',
         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
         evaluation_id: evalId,
         new_state: newState
      })
   })
      .then(response => response.json())
      .then(data => {
         if (data.success) {
            console.log("État de l'évaluation mis à jour avec succès.");
            // Vous pouvez également mettre à jour l'interface utilisateur ici si nécessaire
         } else {
            console.error("Erreur lors de la mise à jour de l'état :", data.message);
         }
         return data;
      })
      .catch(error => {
         console.error("Une erreur s'est produite lors de la mise à jour de l'état :", error);
         throw error;
      });
}

function OnThisBtn(btn) {
   console.log('btn', btn);
   changeTab(btn);
}

window.finishEvaluation = function (studentId, status, button = null) {
   // Trouver l'élément correspondant à l'évaluation
   const evalElement = document.querySelector(`#idStudent-${studentId}-visible [id^="id_eval-"]`);

   if (!evalElement) {
      handleError(`Évaluation introuvable pour l'étudiant ID: ${studentId}`);
      return;
   }

   const id_eval = evalElement.id.split('-').pop();
   const isTeacher = typeof state !== 'undefined' ? state.isTeacher : false;

   const data = {
      evaluationId: id_eval,
      isTeacher: isTeacher,
      status: status
   };

   console.log(`Envoi de la requête pour la transition de l'évaluation (${status})...`, data);
   if (button) button.disabled = true;

   fetch('/api/evaluation/transition', {
      method: 'POST',
      headers: {
         'Content-Type': 'application/json',
         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify(data),
   })
      .then(async response => {
         const result = await response.json();
         if (!response.ok) throw result;

         console.log(`✅ Évaluation ${id_eval} marquée comme ${status}`);
         // Optionnel : afficher un indicateur de succès
         evalElement.insertAdjacentHTML('beforeend', '<div class="text-green-600 mt-2">✔ Évaluation complétée</div>');

         setTimeout(() => {
            evalElement.style.transition = "opacity 0.5s";
            evalElement.style.opacity = 0;
            setTimeout(() => evalElement.remove(), 300);
         }, 700);

         if (button) button.disabled = false;
      })
      .catch(error => {
         handleError(error);
         if (button) button.disabled = false;

         // Optionnel : retour visuel d’erreur
         evalElement.insertAdjacentHTML('beforeend', '<div class="text-red-600 mt-2">❌ Erreur lors de la finalisation</div>');
      });

   console.log(`[Transition] Étudiant ${studentId}, Évaluation ${id_eval}, Rôle ${isTeacher ? 'Enseignant' : 'Élève'}, Action : ${status}`);
};


// Fonction pour gérer la réponse de l'API
function handleResponse(response) {
   if (!response.ok) {
      throw new Error(`Erreur HTTP: ${response.status}`);
   }
   return response.json();
}

// Fonction pour gérer les erreurs
function handleError(error) {
   console.error('Erreur lors de la transition :', error);
   alert(`Une erreur est survenue : ${error.message}`);
}

// Fonction de mise à jour de la visibilité des catégories
function updateVisibilityCategories(idElem, isVisible) {
   const updateVisibilityElement = idElem.replace("-container", "");
   state.visibleCategories[updateVisibilityElement] = isVisible;
}

// Fonction utilitaire pour gérer la visibilité de chaque élément
function toggleVisibilityElement(element, isVisible, toggleButton) {
   // Si l'élément n'existe pas, on ne fait rien
   if (!element) return;

   // Changer la visibilité du contenu
   element.style.display = isVisible ? 'none' : 'grid';
   // Mettre à jour le texte du bouton (flèche)
   toggleButton.textContent = isVisible ? '▼' : '▲';
}

// Fonction utilitaire pour gérer la visibilité de l'élément cible et du bouton de bascule
function toggleContentVisibility(divContainer, student) {
   const contentElementTag = student ? 'form' : 'div'; // Si c'est un étudiant, basculer un formulaire
   const divContent = divContainer.querySelector(contentElementTag);
   const toggleButton = divContainer.querySelector('button');
   const isVisible = divContent.style.display !== 'none'; // Vérifier si l'élément est visible

   // Utiliser la fonction partagée pour basculer la visibilité
   toggleVisibilityElement(divContent, isVisible, toggleButton);

   // Mettre à jour la visibilité dans l'état global
   updateVisibilityCategories(divContainer.id, !isVisible);
}

// window.toggleVisibility = function (divToggle, student = false) {
//    // Si 'all' est passé, on effectue un toggle sur tous les éléments correspondant à idStudent
//    if (divToggle === 'all') {
//       const divStudents = document.querySelectorAll('[id^="idStudent-"]');

//       divStudents.forEach(studentDiv => {
//          toggleContentVisibility(studentDiv, student);
//       });

//       return; // Sortie ici pour ne pas affecter la suite de la logique
//    }
//    // Si nous avons un divToggle, basculer sa visibilité
//    const divContainer = document.getElementById(divToggle);
//    if (divContainer) {
//       toggleContentVisibility(divContainer, student);
//    }
//    // Cas spécifique à un étudiant
//    if (student) {
//       const studentId = divToggle.split('-')[1]; // On récupère l'id de l'étudiant
//       const divSmallFinalResult = document.getElementById(`id - ${ studentId } - small_finalResult`);

//       // Alterner la visibilité de divSmallFinalResult (petit résultat final)
//       if (divSmallFinalResult) {
//          const isHidden = divSmallFinalResult.classList.contains('hidden');
//          divSmallFinalResult.classList.toggle('hidden', !isHidden);
//          divSmallFinalResult.classList.toggle('flex', isHidden);
//       }
//       return; // Sortie pour ne pas affecter le reste du code
//    }
// };


/**
 * Fonction qui permet d'exclure ou d'inclure un étudiant en fonction de l'état des boutons 
 * dans une div spécifique.
 * @param {HTMLElement} btn - Le bouton cliqué qui déclenche la fonction.
 * La fonction parcourt tous les boutons de type 'button' dans la div contenant le bouton cliqué
 * et calcule les résultats finaux si un bouton avec la classe buttonClass est trouvé.
 */
window.toggleExclusion = function (btn) {

   const buttonClass = state.isTeacher ? 'btn-secondary' : 'btn-primary';
   const btnId = btn.id.split('-')[1];

   // Sélectionne la div spécifique contenant les boutons
   const divbtn = document.querySelector(`#id - ${btnId} - btn`);
   if (divbtn) {
      // Sélectionne tous les boutons à l'intérieur de la div
      const btns = divbtn.querySelectorAll('[type=button]');
      btns.forEach(button => {
         if (button.classList.contains(buttonClass)) {
            const studentId = button.id.split('-')[1];
            const level = button.dataset.level;

            calculateFinalResults(studentId, level);
         }
      });
   } else {
      console.error('Div not found');
   }
};

// #region: Load jsonSave

function loadFrom(js) {
   const appreciations = js.evaluations;
   if (!Array.isArray(appreciations) || appreciations.length === 0) {
      console.error(`Aucune appréciation trouvée pour l'étudiant ${js.student_id}.`);
      return;
   }

   appreciations.forEach(app => {
      try {
         if (!app.level) {
            console.warn("Appréciation sans niveau détectée :", app);
            return;
         }

         loadFromJsonSave(js, app.level);
      } catch (error) {
         console.error(`Erreur lors du chargement de l'appréciation (${app.level}) pour l'étudiant ${js.student_id} :`, error);
      }
   });
}

function loadFromJsonSave(js, level) {
   const currentAppreciation = js.evaluations.find(app => app.level === level);

   if (!currentAppreciation) {
      console.warn(`Aucune appréciation trouvée pour le niveau ${level} chez l'étudiant ${js.student_id}`);
      return;
   }

   // 🟢 Remarque générale
   setGeneralRemark(js.student_id, currentAppreciation.student_remark);

   // 🟢 Sélection du bon bouton
   const buttons = document.querySelectorAll(`#id-${js.student_id}-btn > button`);
   buttons.forEach(button => {
      if (button.dataset.level === level) {
         button.classList.add('selected');
      } else {
         button.classList.remove('selected');
      }
   });

   // 🟢 Chargement des critères
   const categoryDivs = document.querySelectorAll(`#idStudent-${js.student_id}-visible > form > .categories-container`);
   categoryDivs.forEach(categoryDiv => {
      const criterionCards = categoryDiv.querySelectorAll('.criterion-card');

      criterionCards.forEach(card => {
         const criterionId = parseInt(card.querySelector('[data-criterion-id]').dataset.criterionId, 10);
         const criterion = currentAppreciation.criteria.find(crit => parseInt(crit.id, 10) === criterionId);

         if (criterion) {
            const slider = card.querySelector(`input[type="range"][data-level="${level}"]`);
            if (slider) {
               slider.parentElement.style.display = 'flex';
               slider.value = criterion.value;
               // Afficher la zone remarque si valeur faible (NA/PA)
               if (!isNaN(parseInt(criterion.value)) && parseInt(criterion.value) < 2) {
                  const t = card.querySelector('textarea');
                  if (t) {
                     t.classList.remove('hidden');
                     const toggle = card.querySelector(`input.swap-input[data-remark-id='${criterionId}']`);
                     if (toggle) toggle.checked = true;
                  }
               }
            }

            const checkbox = card.querySelector('input[type="checkbox"]');
            if (checkbox) checkbox.checked = criterion.checked;

            const textarea = card.querySelector('textarea');
            if (textarea) {
               textarea.value = criterion.remark;
               if (criterion.remark && criterion.remark.trim().length > 0) {
                  textarea.classList.remove('hidden');
                  const toggle = card.querySelector(`input.swap-input[data-remark-id='${criterionId}']`);
                  if (toggle) toggle.checked = true;
               }
            }
         }
      });
   });

   calculateFinalResults(js.student_id, level, 'saved');
}

function setGeneralRemark(studentId, remark) {
   const remarkElement = document.querySelector(`#id-${studentId}-generalRemark`);
   if (remarkElement) remarkElement.value = remark || '';
}

// #endregion

// #region: Submitbutton
function makeToJsonSave(js) {

   // Ajouter la remarque générale de l'étudiant
   js.student_remark = getGeneralRemark(js.student_id);

   //js.worker_contract_id

   // Sélection des boutons associés à l'étudiant
   const selectedButtons = Array.from(document.querySelectorAll(`#id-${js.student_id}-btn > button`))
      .filter(btn => !btn.classList.contains('btn-outline'));

   // Validation de la sélection d'onglet
   if (selectedButtons.length > 1) {
      displayError(js.student_id, "Plusieurs onglets sont sélectionnés. Veuillez corriger.");
      return false;
   }
   if (selectedButtons.length === 0) {
      displayError(js.student_id, "Veuillez sélectionner un onglet avant de soumettre.");
      return false;
   }

   // Déterminer le niveau de l'onglet sélectionné
   const selectedLevel = selectedButtons[0].dataset.level;

   // Initialiser le tableau des appréciations
   const appreciations = [];
   let criterias = [];

   // Récupérer toutes les catégories associées à l'étudiant
   const elemCats = `#idStudent-${js.student_id}-visible > form > .categories-container`;
   const categoryDivs = document.querySelectorAll(elemCats);

   console.log(`Nombre de catégories trouvées pour l'étudiant ${js.student_id} :`, categoryDivs.length);


   categoryDivs.forEach((categoryDiv) => {
      const criterionCards = categoryDiv.querySelectorAll('.criterion-card');

      // Récupération des critères pour chaque carte
      const categoryCriterias = Array.from(criterionCards).map((card) => {
         const selectRanges = card.querySelector('.slider-container');
         const slider = selectRanges?.querySelectorAll('input[type="range"]')[getEvaluationLevelIndex(selectedLevel)];

         // Vérifie si un slider existe
         if (!slider) {
            console.warn(`Aucun slider trouvé pour le critère dans la carte :`, card);
            return false; // Retourne null pour cette carte si elle est incomplète
         }

         const criterionId = slider.dataset.criterionId || null;

         // Vérifie si le criterionId est valide
         if (!criterionId) {
            console.warn(`Aucun ID de critère trouvé pour le slider de cette carte :`, card);
            return false; // Retourne null si l'ID est manquant
         }

         // Récupère le nom du critère
         const criterionName = card.querySelector('[data-criterion-name]')?.dataset.criterionName || 'Nom inconnu';

         // Récupère la valeur du slider, avec un traitement pour s'assurer que c'est un entier entre 0 et 3
         const rawValue = parseInt(slider.value, 10);
         const value = isNaN(rawValue) ? 0 : Math.max(0, Math.min(rawValue, 3)); // Définit une valeur par défaut de 0 en cas d'erreur

         // Récupère l'état de la checkbox et la remarque
         const checked = card.querySelector('input[type="checkbox"]')?.checked || false;
         const remark = card.querySelector('textarea')?.value.trim() || '';

         return {
            id: parseInt(criterionId, 10), // Convertit criterionId en entier si nécessaire
            name: criterionName,
            value: value,
            checked: Boolean(checked), // Convertit l'état de la case en valeur booléenne
            remark: remark
         };
      }).filter(Boolean); // Filtrer les éléments null (qui représentent des cartes incomplètes)

      // Ajoute les critères récupérés de la catégorie au tableau global
      criterias = criterias.concat(categoryCriterias);
   });

   // Vérification si les critères sont vides
   if (criterias.length === 0) {
      displayError(js.student_id, "Aucun critère valide trouvé pour l'évaluation.");
      return false;
   }

   // Fonction pour convertir la date au format souhaité (Y-m-d H:i:s)
   function formatDate(date) {
      let formattedDate = new Date(date); // Crée un objet Date à partir de la date ISO 8601
      return formattedDate.toISOString().slice(0, 19).replace("T", " "); // Format: Y-m-d H:i:s
   }

   // Ajouter les critères associés à l'appréciation
   appreciations.push({
      date: formatDate(new Date()), // Utilise la fonction pour formater la date
      level: selectedLevel,
      criteria: criterias
   });

   // Mettre à jour le bloc "evaluations" pour que le backend voie le contenu
   js.evaluations = {
      status_eval: js.status_eval || 'not_evaluated',
      appreciations: appreciations
   };

   // Assurez-vous que les données sont correctes avant d'envoyer
   displayError(js.student_id, 'Données prêtes pour l’envoi.');;



   return true;

}

// Fonction principale pour ajouter les écouteurs d'événements aux boutons de soumission
function addSubmitButtonListeners(submitBtns) {
   submitBtns.forEach(submitBtn => {
      submitBtn.addEventListener('click', handleSubmitButtonClick);
   });
}

// Fonction pour gérer le clic sur le bouton de soumission
function handleSubmitButtonClick(event) {
   event.preventDefault(); // Empêche l'envoi immédiat du formulaire

   const studentId = getStudentIdFromButton(event.target);
   const isUpdate = getIsUpdateFromButton(event.target);

   const studentData = getStudentData(studentId);
   if (!studentData) {
      handleMissingStudentData(studentId);
      return;
   }

   console.log(`Données de l'élève récupérées :`, studentData);

   //  Récupère le statut d’évaluation actuel depuis la div des onglets
   const currentStatus = document
      .querySelector(`#id-${studentId}-btn`)
      ?.dataset.currentState || 'not_evaluated';

   // On ajoute ce statut dans les données envoyées
   studentData.status_eval = currentStatus;

   console.log(`Statut courant détecté pour l'élève ${studentId} :`, currentStatus);

   //  Construction du JSON à envoyer
   if (!makeToJsonSave(studentData)) {
      const errorMessage = '⚠️ Veuillez sélectionner un type d’évaluation ou valider l’évaluation reçue.';
      console.error(`Erreur pour l'étudiant ${studentId} : ${errorMessage}`);
      displayError(studentId, errorMessage);
      return;
   }

   studentData.isUpdate = isUpdate;

   // Conversion en JSON
   const jsonData = convertToJsonString(studentData);
   updateEvaluationDataTextarea(studentId, jsonData);
   updateEvaluationDataField(studentId, jsonData);

   // Récupération du formulaire parent
   const form = getParentForm(event.target);
   if (!form) {
      handleMissingForm(studentId, event.target.id);
      return;
   }

   // Validation HTML du formulaire
   if (!form.checkValidity()) {
      validateForm(form, studentId);
      return;
   }

   // Log avant envoi
   console.log(`Formulaire prêt à être soumis pour l'élève ${studentId} :`, jsonData);

   // Soumission finale
   form.submit();
   displayError(studentId, `Formulaire soumis avec succès pour l'élève ${studentId}`);
}


function validateForm(form, studentId) {
   if (!form.checkValidity()) {
      // Optionnellement, pour afficher les erreurs natives de validation du navigateur
      form.reportValidity();

      // Afficher un message d'erreur générique
      displayError(studentId, "Le formulaire ne passera pas la validation");

      return false;
   }

   return true;
}


function getIsUpdateFromButton(btn) {
   return btn.dataset.update;
}

// Fonction pour extraire l'ID de l'élève depuis l'ID du bouton
function getStudentIdFromButton(btn) {
   return btn.id.split('-')[1];
}

// Fonction pour récupérer les données de l'étudiant correspondant
function getStudentData(studentId) {
   if (!Array.isArray(state.jsonSave)) {
      console.error("jsonSave n'est pas un tableau d'objets.");
      return null;  // Retourne null si ce n'est pas un tableau
   }

   const student = state.jsonSave.find(attribut => attribut.student_id == studentId);
   if (!student) {
      console.error(`Aucune donnée trouvée pour l'étudiant avec ID: ${studentId}`);
      return null;
   }
   return student;
}


// Fonction pour gérer les cas où les données de l'étudiant sont manquantes
function handleMissingStudentData(studentId) {
   console.warn(`Aucune donnée trouvée pour l'élève ID: ${studentId}`);
   displayError(studentId, `Données manquantes pour l'élève ID: ${studentId}`);
}

// Fonction pour convertir l'objet en chaîne JSON et supprimer les espaces en début et fin de chaîne
function convertToJsonString(data) {
   return JSON.stringify(data).replace(/^\s+|\s+$/g, '');
}

// Fonction pour mettre à jour le textarea avec les données JSON
function updateEvaluationDataTextarea(studentId, jsonData) {
   const evaluationDataTextarea = document.getElementById(`evaluation-data-${studentId}`);
   if (evaluationDataTextarea) {
      evaluationDataTextarea.value = jsonData;
      console.log(`Données insérées dans le textarea pour l'élève ID ${studentId} :`, evaluationDataTextarea.value);
   } else {
      console.error(`Aucun textarea trouvé pour l'élève ID ${studentId}.`);
      displayError(studentId, `Le champ textarea pour les données d'évaluation est manquant pour l'élève ID: ${studentId}`);
   }
}

// Fonction pour mettre à jour le champ caché avec les données JSON
function updateEvaluationDataField(studentId, jsonData) {
   const evaluationDataField = document.getElementById(`evaluation-data-${studentId}`);
   if (evaluationDataField) {
      evaluationDataField.value = jsonData;
      console.log(`Données insérées dans le champ caché pour l'élève ID ${studentId} :`, evaluationDataField.value);
   } else {
      console.warn(`Aucun champ caché trouvé pour l'élève ID: ${studentId}`);
      displayError(studentId, `Champ caché pour les données d'évaluation manquant pour l'élève ID: ${studentId}`);
   }
}

// Fonction pour obtenir le formulaire parent du bouton
function getParentForm(button) {
   return button.closest('form');
}

// Fonction pour gérer les cas où le formulaire est manquant
function handleMissingForm(studentId, buttonId) {
   console.error(`Aucun formulaire trouvé pour le bouton avec ID : ${buttonId}`);
   displayError(studentId, `Formulaire de soumission introuvable pour l'élève ID: ${studentId}`);
}

// #endregion

// #region détermination du résultat

/**
 * Calcule et affiche le résultat final pour un étudiant selon les critères sélectionnés.
 *
 * @param {number|string} student_id - ID de l'étudiant.
 * @param {string} levelName - Niveau d’évaluation ("autoFormative", "evalFormative", "autoFinale", "evalFinale").
 * @param {string} [resultType='live'] - Type d'affichage ("live" ou "saved").
 */
function calculateFinalResults(student_id, levelName, resultType = 'live') {
   // Vérifie la présence des données nécessaires
   if (!state?.criteriaGrouped || !state?.evaluationLabels || !state?.evaluationShortLabels) {
      console.warn('calculateFinalResults: données d’état manquantes.');
      return;
   }

   let count = 8;
   let totalScores = 0;
   let naCount = 0, paCount = 0, aCount = 0, laCount = 0;
   let result = '';
   let bgClass = '';

   const divSmallFinalResult = document.getElementById(`id-${student_id}-small_finalResult`);
   const divFinalResult = document.getElementById(`id-${student_id}-finalResult-${resultType}`);
   if (!divFinalResult || !divSmallFinalResult) return;

   const sliders = document.querySelectorAll(
      `input[type="range"][data-level="${levelName}"][data-student-id="${student_id}"]`
   );
   const checkboxes = document.querySelectorAll(
      `input[type="checkbox"][data-student-id="${student_id}"]`
   );

   // Titres dynamiques depuis le backend (PHP)
   const finalResultTitle = state.evaluationLabels?.[levelName] ?? 'Erreur';
   const smallFinalResultTitle = (state.evaluationShortLabels?.[levelName] ?? 'X') + ': ';

   // Détermine si c’est une évaluation formative ou finale
   const spanResult = levelName.toLowerCase().includes('finale') ? '100%' : '>79%';

   // Met à jour les titres dans le DOM
   divFinalResult.querySelector(`#finalResultTitle-${student_id}-${resultType}`).innerHTML = finalResultTitle;
   divSmallFinalResult.querySelector(`#smallResultTitle-${student_id}`).innerHTML = smallFinalResultTitle;
   divFinalResult.querySelector(`#spanResult-${student_id}-${resultType}`).innerHTML = spanResult;

   // Calcul des résultats
   Object.entries(state.criteriaGrouped).forEach(([categoryName, crits]) => {
      crits.forEach(crit => {
         const isExcluded = Array.from(checkboxes).some(checkbox =>
            checkbox.dataset.excludeId === `${crit.position}` &&
            checkbox.dataset.studentId === `${student_id}` &&
            checkbox.checked
         );
         if (isExcluded) {
            count--;
            return;
         }

         const slider = Array.from(sliders).find(
            s => s.dataset.criterionId === `${crit.position}`
         );
         if (slider) {
            const value = parseInt(slider.value, 10);
            if (isNaN(value)) return;

            totalScores += value;
            if (value < 1) naCount++;
            else if (value < 2) paCount++;
            else if (value < 3) aCount++;
            else laCount++;
         }
      });
   });

   // 🔹 Détermination du résultat global
   if (naCount > 0) {
      result = state.appreciationLabels[0];
      bgClass = 'bg-red-200';
   } else if (paCount > 0) {
      result = state.appreciationLabels[1];
      bgClass = 'bg-yellow-200';
   } else if (aCount > Math.floor(count / 2)) {
      result = state.appreciationLabels[2];
      bgClass = 'bg-green-200';
   } else {
      result = state.appreciationLabels[3];
      bgClass = 'bg-blue-200';
   }

   // 🔹 Nettoyage et application du fond
   const cleanBg = el => el.classList.forEach(c => c.startsWith('bg-') && el.classList.remove(c));
   cleanBg(divFinalResult);
   cleanBg(divSmallFinalResult);

   divFinalResult.classList.add(bgClass);
   divSmallFinalResult.classList.add(bgClass);

   // 🔹 Affichage du résultat final
   divFinalResult.querySelector(`#finalResultContent-${student_id}-${resultType}`).innerHTML = result;
   divSmallFinalResult.querySelector('#smallResultContent').innerHTML = result;
   divFinalResult.classList.replace('hidden', 'flex');
}

// Helper: vérifie si l'élève a une auto‑évaluation sommative (ELEV‑S) enregistrée
function hasStudentAFormative2(studentId) {
   try {
      const js = getStudentData(studentId);
      if (!js || !Array.isArray(js.evaluations)) return false;
      return js.evaluations.some(app => (app.level === 'aFormative2'));
   } catch (_) {
      return false;
   }
}


// #endregion

// #region: toDoList
let todo_listAdd = [];
let todo_listRemove = [];

// Ajouter une nouvelle tâche
window.addTodoItem = function (btn) {

   // const areaRemark = document.getElementById('remark-general-area');
   const container = btn.closest('#todo-list-container');
   const newItem = document.createElement('div');

   if (todo_listRemove.length === 0) {
      todo_listAdd.push(btn.parentElement.children.length - 3);
      newItem.id = btn.parentElement.children.length - 3;
   } else {
      newItem.id = todo_listRemove.pop();
   }

   newItem.className = 'todo-item flex items-center space-x-2 mx-3 my-1';
   newItem.innerHTML = `<input type ="checkbox" class="checkbox" >
      <input type="text" class="input input-bordered flex-1 text-gray-900 dark:text-gray-200  dark:bg-gray-700">
         <button type="button" class="btn" onclick="removeTodoItem(this)">
            <i class="fas fa-trash-alt m-0 text-red-600 text-lg"></i>
         </button>`;

   // Si vous préférez le texte ... =>  ${window.LangRemoveTask}

   // Sélectionnez l'élément avec l'ID 'msgTodo' parmi les enfants de btn.parentElement
   const msgTodoElement = btn.parentElement.querySelector('#msgTodo');

   // Vérifiez si l'élément existe et ajoutez la classe 'hidden'
   if (msgTodoElement) {
      msgTodoElement.classList.add('hidden');
   }
   container.insertBefore(newItem, btn);
};

// Supprimer une tâche
window.removeTodoItem = function (btn) {
   const item = btn.closest('.todo-item');
   const id = item.id;
   todo_listRemove.push(id);
   item.remove();
};



//#region pdf and print 

async function fillAndSavePdf(data, type = 'formative', filename = 'evaluation.pdf') {
   try {
      const existingPdfUrl = `/pdf-template/${type}`;
      const existingPdfBytes = await fetch(existingPdfUrl).then(res => res.arrayBuffer());
      const pdfDoc = await PDFDocument.load(existingPdfBytes);
      const form = pdfDoc.getForm();

      const allFields = form.getFields().map(field => field.getName());
      console.log('Champs trouvés dans le PDF :', allFields);

      const fieldMapping = {
         student_name: data.student_name,
         teacher_name: data.teacher_name,
         class_name: data.class_name,
         project_name: data.project_name,
         weeks: data.weeks,
         dates: data.dates,
         generalRemark: data.generalRemark,
         resultFinal: data.resultFinal,
         ...Object.fromEntries(Array.from({ length: 8 }, (_, i) => [
            `criterion_${i + 1}_name`, data[`criterion_${i + 1}_name`] || ''
         ])),

      };

      state.evaluationLevels.forEach(level => {
         for (let i = 1; i <= 8; i++) {
            state.appreciationLabels.forEach(suffix => {
               const fieldName = `${level}_${i}_${suffix}`;
               fieldMapping[fieldName] = data[fieldName] || '';
            });
         }
      });

      // Remplissage sécurisé
      for (const [name, value] of Object.entries(fieldMapping)) {
         if (allFields.includes(name)) {
            try {
               const field = form.getField(name);
               const fieldType = field.constructor.name;

               if (fieldType === 'PDFTextField' || fieldType === 'PDFTextField2') {
                  field.setText((value ?? '').toString());
               } else if (fieldType === 'PDFRadioGroup' || fieldType === 'PDFRadioGroup2') {
                  field.select((value ?? '').toString());
               } else {
                  console.warn(`Champ "${name}" : type non géré (${fieldType})`);
               }

            } catch (err) {
               console.warn(`Erreur lors du remplissage du champ "${name}":`, err.message);
            }
         } else {
            console.warn(`Champ "${name}" non présent dans le formulaire PDF`);
         }
      }

      form.flatten();

      const pdfBytes = await pdfDoc.save();
      const pdfBlob = new Blob([pdfBytes], { type: 'application/pdf' });

      const reader = new FileReader();
      reader.onloadend = function () {
         const base64data = reader.result;

         fetch('/save-filled-pdf', {
            method: 'POST',
            headers: {
               'Content-Type': 'application/json',
               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
               pdf: base64data,
               filename: filename
            })
         })
            .then(response => response.json())
            .then(result => {
               if (result.success) {
                  alert("PDF enregistré : " + result.path);
                  window.open(`/${result.path}`, '_blank');
               } else {
                  alert("Erreur lors de l'enregistrement du PDF");
               }
            });
      };

      reader.readAsDataURL(pdfBlob);

   } catch (error) {
      console.error("Erreur globale dans fillAndSavePdf :", error);
      alert("Une erreur est survenue lors du traitement du PDF.");
   }
}


/**
 * On construit un objet critère 
 *  - nom du critère 
 *  - Radio Auto 80 / 100 (na, pa, a, la)
 *  - Radio Eval 80 / 100 (na, pa, a, la)
 * @returns critère
 */
function getCriteriaValues() {
   const criteria = [];

   document.querySelectorAll('[data-criterion-name]').forEach((element, index) => {
      const criterionName = element.dataset.criterionName;

      const containerRanges = element.nextElementSibling;
      const containerRemark = containerRanges.nextElementSibling;

      const criterionRemark = containerRemark.querySelector('textarea')?.value || '';

      const evals = {
         auto_formative: containerRanges.children[0]?.querySelector('input')?.value || '',
         eval_formative: containerRanges.children[1]?.querySelector('input')?.value || '',
         auto_finale: containerRanges.children[2]?.querySelector('input')?.value || '',
         eval_finale: containerRanges.children[3]?.querySelector('input')?.value || ''
      };

      criteria.push({
         id: index + 1,
         name: criterionName,
         values: evals,
         remark: criterionRemark
      });
   });

   return criteria;
}

const truncate = (str, max = 25) => str.length > max ? str.slice(0, max - 3) + '...' : str;

window.printSection = function (button) {

   let student_id = button.dataset.printId.match(/(\d+)$/)[0];
   const studentData = getStudentData(student_id);

   const criteriaValues = getCriteriaValues();

   if (studentData) {
      console.log('studentData: ', studentData);
      const fillData = {
         student_name: truncate(`${studentData.student_firstname} ${studentData.student_lastname}`),
         class_name: studentData.student_class_name,
         teacher_name: truncate(studentData.evaluator_name),
         project_name: truncate(studentData.job_title),
         weeks: "Semaines 1 à 8",
         dates: `${studentData.project_start.split(' ')[0]} - ${studentData.project_end.split(' ')[0]}`,
         generalRemark: studentData.evaluations.student_remark,
         resultFinal: document.getElementById(`finalResultContent-${studentData.student_id}-saved`).textContent,

      };

      criteriaValues.forEach((criterion, index) => {
         const idx = index + 1;
         const criterionKey = `criterion_${idx}_name`;
         fillData[criterionKey] = criterion.name;

         const values = criterion.values;
         const labels = ['NA', 'PA', 'A', 'LA'];

         Object.entries(values).forEach(([level, val]) => {
            const intVal = parseInt(val);
            if (!isNaN(intVal) && intVal >= 0 && intVal < labels.length) {
               const suffix = labels[intVal];
               const fieldName = `${level}_${idx}_${suffix}`; // ex: auto80_1_A
               fillData[fieldName] = 'Yes'; // coche le bouton radio correspondant
            }
         });

         fillData[`criterion_${idx}`] = criterion.remark || '';
      });

      fillAndSavePdf(fillData, 'summative', `${studentData.student_lastname}_${studentData.student_firstname}_formative.pdf`);
   }
}

/* fonction pour impression depuis css... */
function print(btn) {
   // Récupérer l'ID du bouton (par exemple "student_id-4")
   const printTargetId = button.dataset.printId;
   const printTarget = document.querySelector(`[data-print-target="${printTargetId}"]`);

   // Vérifier si l'élément à imprimer est trouvé
   if (!printTarget) {
      console.error(`❌ Élément à imprimer introuvable pour printTargetId: ${printTargetId}`);
      return;
   }

   // Ouvrir une nouvelle fenêtre pour l'impression
   const printWindow = window.open("", "_blank");
   // const printContent = printTarget.innerHTML;
   const printContent = printTarget.outerHTML;


   // Ajouter le fichier CSS spécifique à l'impression (vérifiez le chemin d'accès au fichier CSS)
   const printStylesheetLink = `<link rel="stylesheet" href="${window.location.origin}/css/printed_fullevaluation.css" media="print">`;

   // Vous pouvez également inclure des règles CSS spécifiques pour l'impression ici, au cas où le fichier CSS externe ne serait pas disponible
   const printStyles = `
      <style>
         @media print {
            body {
               font-family: Arial, sans-serif;
               font-size: 12pt;
               color: #000;
               background: #fff;
            }
            /* Ajoutez ici vos styles d'impression supplémentaires */
         }
         </style >
   `;

   // Écrire le contenu à imprimer dans la nouvelle fenêtre
   printWindow.document.write(`
   < !DOCTYPE html >
      <html>
         <head>
            <title>Impression</title>
            ${printStylesheetLink} <!-- Lien vers le fichier CSS d'impression -->
            ${printStyles} <!-- Styles supplémentaires pour l'impression -->
         </head>
         <body>${printContent}</body>
      </html>
`);

   printWindow.document.close(); // Fermer le document pour s'assurer que le contenu est chargé

   // Attendre un peu avant d'imprimer pour être sûr que tout est chargé
   printWindow.focus();  // Se concentrer sur la fenêtre d'impression
   setTimeout(() => {
      printWindow.print();  // Imprimer
      printWindow.close();  // Fermer la fenêtre après l'impression
   }, 300);
}






