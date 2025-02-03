const state = window.evaluationState;

document.addEventListener('DOMContentLoaded', function () {

   console.log(state);

   handleTabSwitch();

   // Gestion des boutons de soumission
   const submitBtns = document.querySelectorAll('[id^="id-"][id$="-buttonSubmit"]');

   // Sélectionner tous les boutons de soumission
   addSubmitButtonListeners(submitBtns);

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

   if (state.isTeacher) {
      // Cibler les zones de texte avec un clic droit
      document.querySelectorAll('.remark textarea').forEach((textarea) => {
         textarea.addEventListener('contextmenu', (event) => {
            event.preventDefault(); // Empêche le menu contextuel par défaut

            // Basculer en mode "to-do"
            const container = textarea.closest('.remark');
            const todoListContainer = container.querySelector('#todo-list-container');

            textarea.classList.add('hidden'); // Cache la zone de texte
            todoListContainer.classList.remove('hidden'); // Affiche la liste de tâches

            // Ajouter les classes Tailwind nécessaires pour Flexbox 
            todoListContainer.classList.add('flex', 'gap-5', 'flex-wrap');
         });
      });
   }
});

function handleTabSwitch() {
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
   return state.evaluationLevels.indexOf(levelName);
}

function getGeneralRemark(studentId) {
   const idGeneralRemark = `id-${studentId}-generalRemark`;
   const generalRemark = document.getElementById(idGeneralRemark).value.trim();
   return generalRemark;
}

// Fonction pour afficher les erreurs dans le formulaire
function displayError(studentId, message) {
   const errorDiv = document.getElementById(`errors-${studentId}`);
   if (errorDiv) {
      errorDiv.textContent = message;
      errorDiv.classList.remove('hidden');  // Affiche le message d'erreur
   } else {
      console.warn(`Aucun conteneur d'erreur trouvé pour l'élève ID: ${studentId}`);
   }
}

// Mise à jour des résultats des curseurs
window.updateSliderValue = function (slider) {
   const id = slider.id.replace('-range', '-result');
   syncSliders(slider);

   console.log('update: ', id.split('-')[1], id.split('-')[3]);
   calculateFinalResults(id.split('-')[1], id.split('-')[3]);
};


function notifyChangeEval(studentId, domElement) {
   const elemFinalResult = document.getElementById(`id-${studentId}-${domElement}`);
   const elemToDoNext = document.getElementById(`id-${studentId}-nextState-${state.isTeacher ? 'teacher' : 'student'}`);

   if (elemFinalResult) {
      // span a afficher
      const spanNextState = elemFinalResult.querySelector('#nextState');
      // span a cacher
      const spanCurrentState = elemFinalResult.querySelector('#currentState');

      // si le span est trouvé
      if (spanNextState && spanCurrentState) {
         // on cache le span actuel
         spanCurrentState.classList.add('hidden');
         // on affiche le span suivant
         spanNextState.classList.remove('hidden');
      } else {
         console.error(`Element with ID 'id-${studentId}-${domElement}' not found`);
      }
   }

   if (elemToDoNext) {
      // Ajouter des classes pour notifier le changement
      elemToDoNext.classList.remove('top-5');
      elemToDoNext.classList.add('bg-yellow-300', 'text-blue-600', 'text-2xl', 'font-bold', 'p-2', 'rounded', 'bottom-10');


      elemToDoNext.textContent = elemToDoNext.textContent.replace('À faire :', 'Vous faites :');


      // Utiliser requestAnimationFrame pour garantir que les classes sont ajoutées avant de les supprimer
      requestAnimationFrame(() => {
         setTimeout(() => {
            elemToDoNext.classList.remove('bg-yellow-300', 'text-blue-600', 'text-2xl', 'font-bold', 'p-2', 'rounded', ' bottom-10');
            elemToDoNext.classList.add('top-5');
         }, 5000);
      });

   } else {
      console.error(`Element with ID 'id-${studentId}-nextState' not found`);
   }
}

// Fonction qui permet de changer l'onglet (eval80 vs eval100)
window.changeTab = function (onClickBtn, indexCalByLoad = null) {

   const TAB_80 = '80';
   const TAB_100 = '100';
   const tabName = onClickBtn.id.replace('btn', 'range');
   const studentId = onClickBtn.id.split('-')[1];
   const buttonClass = state.isTeacher ? 'btn-secondary' : 'btn-primary';
   // console.log(indexCalByLoad, !state.isTeacher);


   notifyChangeEval(studentId, 'finalResult');

   // a travailler, le but etant de faire un popup pour dire que l'evaluation a été modifié...
   if (indexCalByLoad !== null && !state.isTeacher) {
      // console.log('dans changeTab : ', indexCalByLoad);

      // Calcule les résultats finaux
      calculateFinalResults(studentId, state.evaluationLevels[indexCalByLoad]);

      // Désactiver tous les éléments de type "range" (sliders) présents sur la page.
      const ranges = document.querySelectorAll('[type=range]');
      ranges.forEach(range => {
         range.disabled = true;
      });

      // Affiche les consignes à suivre
      if (indexCalByLoad <= 2) {
         alert(
            'Votre enseignant a mis à jour votre évaluation formative.\n' +
            'Merci de consulter la note. ' +
            'Si demandé, vous pouvez créer une auto-évaluation supplémentaire en cliquant sur auto100.'
         );
      } else {
         alert(
            'Votre enseignant a mis à jour votre évaluation sommative.\n' +
            'Merci de consulter la note finale.'
         );
      }
   }
   else {

      let idsRangesDisabled = `[id^="${tabName}-"]`;

      if (onClickBtn.classList.contains(buttonClass)) {
         onClickBtn.classList.remove(buttonClass);
      }

      // Détermine le nouvel onglet et met à jour les classes CSS
      let idsRangesEnabled;
      if (idsRangesDisabled.includes(TAB_80)) {
         idsRangesEnabled = idsRangesDisabled.replace(TAB_80, TAB_100);
         onClickBtn.classList.remove('btn-outline');
         onClickBtn.classList.add(buttonClass);
         document.getElementById(onClickBtn.id.replace(TAB_80, TAB_100)).classList.add('btn-outline');
      } else {
         idsRangesEnabled = idsRangesDisabled.replace(TAB_100, TAB_80);
         onClickBtn.classList.remove('btn-outline');
         onClickBtn.classList.add(buttonClass);
         document.getElementById(onClickBtn.id.replace(TAB_100, TAB_80)).classList.add('btn-outline');
      }

      // Active/Désactive les éléments des onglets
      const divsDisabled = document.querySelectorAll(idsRangesDisabled);
      const divsEnabled = document.querySelectorAll(idsRangesEnabled);

      divsDisabled.forEach(div => { div.disabled = false; });
      divsEnabled.forEach(div => { div.disabled = true; });

      // Calcule les résultats finaux
      calculateFinalResults(studentId, onClickBtn.dataset.level);
   }

};


// Fonction de validation des évaluations et affichage des sliders. 
// Active les boutons d'évaluation et affiche les sliders en fonction du rôle de l'utilisateur (enseignant ou
window.validateEvaluation = function (prefix, indexCalByLoad = null) {
   const idStudent = prefix.split('-')[0];
   let idBtn, sliders100Elem, sliders80Elem;

   if (state.isTeacher) {
      idBtn = `id-${prefix}eval100`;
      sliders100Elem = `[id^="id-${idStudent}-eval100-"]`;
      sliders80Elem = `[id^="id-${idStudent}-eval80-"]`;
   } else {
      idBtn = `id-${prefix}auto100`;
      sliders100Elem = `[id^="id-${idStudent}-auto100-"]`;
      sliders80Elem = `[id^="id-${idStudent}-auto80-"]`;
   }

   const btn = document.getElementById(idBtn);
   const divSliders = document.querySelectorAll(sliders100Elem);

   if (btn) {
      btn.disabled = false;
      divSliders.forEach(div => {
         div.style.display = 'flex';
         const range = div.querySelector('input[type="range"]');
         if (range) {
            range.disabled = false;
         }
      });
   }

   changeTab(btn, indexCalByLoad);
};


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

window.toggleVisibility = function (divToggle, student = false) {
   // Si 'all' est passé, on effectue un toggle sur tous les éléments correspondant à idStudent
   if (divToggle === 'all') {
      const divStudents = document.querySelectorAll('[id^="idStudent-"]');

      divStudents.forEach(studentDiv => {
         toggleContentVisibility(studentDiv, student);
      });

      return; // Sortie ici pour ne pas affecter la suite de la logique
   }
   // Si nous avons un divToggle, basculer sa visibilité
   const divContainer = document.getElementById(divToggle);
   if (divContainer) {
      toggleContentVisibility(divContainer, student);
   }
   // Cas spécifique à un étudiant
   if (student) {
      const studentId = divToggle.split('-')[1]; // On récupère l'id de l'étudiant
      const divSmallFinalResult = document.getElementById(`id-${studentId}-small_finalResult`);

      // Alterner la visibilité de divSmallFinalResult (petit résultat final)
      if (divSmallFinalResult) {
         const isHidden = divSmallFinalResult.classList.contains('hidden');
         divSmallFinalResult.classList.toggle('hidden', !isHidden);
         divSmallFinalResult.classList.toggle('flex', isHidden);
      }
      return; // Sortie pour ne pas affecter le reste du code
   }
};


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
   const divbtn = document.querySelector(`#id-${btnId}-btn`);
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

// #region: Laod jsaonSave
function loadFrom(js) {

   // Vérifier les appréciations au sein de la première évaluation
   const appreciations = js.evaluations.appreciations;
   if (!Array.isArray(appreciations) || appreciations.length === 0) {
      console.error(`Aucune appréciation trouvée pour l'étudiant ${js.student_Id}.`);
      return;
   }

   // Charger les données pour chaque niveau d'appréciation
   appreciations.forEach((app, indexLevel) => {
      try {
         loadFromJsonSave(js, indexLevel);

         // // Appeler validateEvaluation uniquement lors du dernier tour
         // if (indexLevel === appreciations.length - 1) {
         //    // Appel de la fonction validateEvaluation afin de mettre à jour 
         //    // l'état des boutons (préfixe = '33-btn-')
         //    validateEvaluation(js.student_Id + '-btn-', js.evaluations.appreciations[indexLevel].level);
         // }
      } catch (error) {
         console.error(`Erreur lors du chargement de l'appréciation niveau ${indexLevel} pour l'étudiant ${js.student_Id} :`, error);
      }
   });

}

function loadFromJsonSave(js, indexLevel) {

   console.log(js.student_Id, js.evaluations.appreciations[indexLevel].level);

   // Mettre à jour la remarque générale de l'étudiant
   setGeneralRemark(js.student_Id, js.evaluations.student_remark);

   // Sélectionner l'onglet en fonction du niveau
   const buttons = document.querySelectorAll(`#id-${js.student_Id}-btn > button`);

   buttons.forEach(button => {
      if (button.dataset.level === state.evaluationLevels[js.evaluations.appreciations[indexLevel].level]) {
         button.classList.remove('btn-outline');
         button.classList.add('selected');

      } else {
         button.classList.remove('selected');
         button.classList.add('btn-outline');
      }
   });

   // Mettre à jour les critères
   const categoryDivs = document.querySelectorAll(`#id-${js.student_Id}-criterias > .category-container`);
   categoryDivs.forEach(categoryDiv => {
      const criterionCards = categoryDiv.querySelectorAll('.criterion-card');

      criterionCards.forEach(card => {
         const criterionId = card.querySelector('[data-criterion-id]').dataset.criterionId;

         const criterion = js.evaluations.appreciations[indexLevel].criteria.find(crit => {

            const critId = parseInt(crit.id, 10);
            const criterionIdInt = parseInt(criterionId, 10);

            console.log(`Comparing crit.id: ${critId} with criterionId: ${criterionIdInt}`);
            return critId === criterionIdInt;
         });

         console.log('Criterion found:', criterion);

         if (criterion) {
            const sliders = card.querySelectorAll('input[type="range"]');
            const slider = Array.from(sliders).find(sl => sl.dataset.level === state.evaluationLevels[js.evaluations.appreciations[indexLevel].level]);

            slider.parentElement.style.display = 'flex';

            if (slider) {
               slider.value = criterion.value;
            }
            const checkbox = card.querySelector('input[type="checkbox"]');
            if (checkbox) {
               checkbox.checked = criterion.checked;
            }
            const textarea = card.querySelector('textarea');

            if (textarea) {
               textarea.value = criterion.remark;
            }
         }
      });
   });
}

// Fonction de mise à jour de la remarque générale (à définir selon votre logique)
function setGeneralRemark(studentId, remark) {
   const remarkElement = document.querySelector(`#id-${studentId}-generalRemark`);

   console.log(`#id-${studentId}-generalRemark`);

   if (remarkElement) {
      remarkElement.value = remark;
   }
}
// #endregion


// #region: Submitbutton
function makeToJsonSave(js) {

   // Ajouter la remarque générale de l'étudiant
   js.student_remark = getGeneralRemark(js.student_Id);

   // Sélection des boutons associés à l'étudiant
   const selectedButtons = Array.from(document.querySelectorAll(`#id-${js.student_Id}-btn > button`))
      .filter(btn => !btn.classList.contains('btn-outline'));

   // Validation de la sélection d'onglet
   if (selectedButtons.length > 1) {
      displayError(js.student_Id, "Plusieurs onglets sont sélectionnés. Veuillez corriger.");
      return false;
   }
   if (selectedButtons.length === 0) {
      displayError(js.student_Id, "Veuillez sélectionner un onglet avant de soumettre.");
      return false;
   }

   // Déterminer le niveau de l'onglet sélectionné
   const selectedLevel = selectedButtons[0].dataset.level;

   // Initialiser le tableau des appréciations
   const appreciations = [];
   let criterias = [];

   // Récupérer toutes les catégories associées à l'étudiant
   const categoryDivs = document.querySelectorAll(`#id-${js.student_Id}-criterias > .category-container`);

   //console.log(`Nombre de catégories trouvées pour l'étudiant ${js.student_Id} :`, categoryDivs.length);

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
      displayError(js.student_Id, "Aucun critère valide trouvé pour l'évaluation.");
      return false;
   }

   // Ajouter les critères associés à l'appréciation
   appreciations.push({
      date: new Date().toISOString(),
      level: selectedLevel,
      criteria: criterias
   });

   // Ajouter les données collectées au JSON final
   js.appreciations = appreciations;

   // Assurez-vous que les données sont correctes avant d'envoyer
   displayError(js.student_Id, 'Données envoyées pour l\'étudiant');
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

   // console.log(`Bouton de soumission cliqué pour l'élève ID: ${studentId}`);
   const studentId = getStudentIdFromButton(event.target);
   const isUpdate = getIsUpdateFromButton(event.target);

   const studentData = getStudentData(studentId);
   if (!studentData) {
      handleMissingStudentData(studentId);
      return;
   }

   // console.log(`Données de l'élève récupérées :`, studentData);
   if (!makeToJsonSave(studentData)) {
      displayError(studentId, 'Veuillez sélectionner un type d\'évaluation.');
      return;
   }

   studentData.isUpdate = isUpdate;

   const jsonData = convertToJsonString(studentData);
   updateEvaluationDataTextarea(studentId, jsonData);
   updateEvaluationDataField(studentId, jsonData);

   const form = getParentForm(event.target);
   if (!form) {
      handleMissingForm(studentId, event.target.id);
      return;
   }

   if (!form.checkValidity()) {
      displayError(studentId, "Le formulaire ne passera pas la validation");
      return;
   }

   //   console.log("Contenu de evaluation_data juste avant l'envoi : ", document.getElementById('evaluation-data-' + studentId).value);

   form.submit();
   displayError(studentId, 'Formulaire soumis pour l\'élève ID:', studentId);
   // console.log("Formulaire prêt à être soumis avec les données : ", document.getElementById('evaluation-data-' + studentId).value);
}

function getIsUpdateFromButton(btn) {
   return Boolean(btn.dataset.update);
}

// Fonction pour extraire l'ID de l'élève depuis l'ID du bouton
function getStudentIdFromButton(button) {
   return button.id.split('-')[1];
}

// Fonction pour récupérer les données de l'étudiant correspondant
function getStudentData(studentId) {
   return state.jsonSave.find(attribut => attribut.student_Id == studentId);
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
//
// Ce n'est pas une bonne solution (que faire si je change de type d'évaluation ?)
//  (function () {
//    // Sélectionne tous les éléments dont l'ID se termine par '-finalResult' 
//    const divResults = document.querySelectorAll('[id$="-finalResult"]');

//    setInterval(() => {
//       divResults.forEach(element => {
//          const studentId = element.id.split('-')[1];
//          const levelName = state.isTeacher ? 'eval80' : 'auto80';
//          console.log('start');
//          calculateFinalResults(studentId, levelName);
//       });
//    }, 2000);
// })();


/**
 * Fonction qui affiche le résultat dans la div #id-{{ $studentDetails->student_id }}-finalResult  
 * et la div #id-{{ $studentDetails->student_id }}-small_finalResult selon le calcul défini par le règlement ETML.
 * Cette fonction parcourt les catégories de critères définies dans 'state.criteriaGrouped',
 * et calcule un score en fonction des valeurs des sliders (évaluations) et de l'exclusion de certains critères via les checkboxes.
 * Le résultat est ensuite affiché en fonction des appréciations prédéfinies dans 'state.appreciationLabels'.
 * 
 * @param {number} student_id - L'ID de l'étudiant pour lequel les résultats sont calculés.
 * @param {string} levelName - Le niveau d'évaluation (ex. "auto80", "eval80", "auto100", "eval100").
 * 
 * @returns {void} Aucun retour ; les résultats sont directement affichés dans les divs correspondantes.
 */
function calculateFinalResults(student_id, levelName) {


   // Variables pour calculer les scores et statistiques
   let count = 8;
   let totalScores = 0;
   let naCount = 0;
   let paCount = 0;
   let aCount = 0;
   let laCount = 0;
   let result = 0;

   // Variable pour le titre
   let finalResultTitle = '';
   let smallFinalResultTitle = '';
   let spanResult = ''

   // pour le fond 
   let bgClass = null;

   // Les divs à traiter
   const divSamllFinalResult = document.getElementById(`id-${student_id}-small_finalResult`);
   const divFinalResult = document.getElementById(`id-${student_id}-finalResult`);

   // Sélectionner les sliders avec les attributs spécifiques
   const sliders = document.querySelectorAll(
      `input[type="range"][data-level="${levelName}"][data-student-id="${student_id}"]`
   );

   // Sélectionner les checkboxes avec les attributs spécifiques
   const checkboxes = document.querySelectorAll(
      `input[type="checkbox"][data-student-id="${student_id}"]`
   );

   // Assigner un titre en fonction du levelName
   switch (levelName) {
      case state.evaluationLevels[0]:
         finalResultTitle = 'A-Formative';
         smallFinalResultTitle = 'A: ';
         spanResult = '80%';
         break;
      case state.evaluationLevels[2]:
         finalResultTitle = 'Formative';
         smallFinalResultTitle = 'F: ';
         spanResult = '80%';

         break;
      case state.evaluationLevels[3]:
         finalResultTitle = 'Sommative';
         smallFinalResultTitle = 'S: ';
         spanResult = '100%';
         break;
      case state.evaluationLevels[1]:
         finalResultTitle = 'A-Sommative';
         smallFinalResultTitle = 'A+: ';
         spanResult = '100%';
         break;
      default:
         finalResultTitle = 'Erreur';
         smallFinalResultTitle = 'X: ';
         spanResult = '404';
         break;
   }

   // Afficher les titres dans les divs correspondantes
   divFinalResult.querySelector(`#finalResultTitle-${student_id}`).innerHTML = finalResultTitle;
   divSamllFinalResult.querySelector(`#smallResultTitle-${student_id}`).innerHTML = smallFinalResultTitle;
   divFinalResult.querySelector('#spanResult').innerHTML = spanResult;

   // Parcours des catégories dans criteriaGrouped
   Object.entries(state.criteriaGrouped).forEach(([categoryName, crits]) => {
      crits.forEach(crit => {
         // Vérifier si le critère est exclu via la checkbox
         const isExcluded = Array.from(checkboxes).some(checkbox => {
            // Vérifie si la checkbox correspond au critère et à l'élève
            return (
               checkbox.dataset.excludeId === `${crit.position}` &&
               checkbox.dataset.studentId === `${student_id}` &&
               checkbox.checked
            );
         });

         if (isExcluded) {
            count--; // Réduire le nombre total attendu si le critère est exclu
            return; // Passer au critère suivant
         }

         // Trouver le slider associé au critère
         const slider = Array.from(sliders).find(slider => {
            const match = slider.dataset.criterionId === `${crit.position}`;
            return match;
         });

         // console.log('slide: ', slider);
         if (slider) {
            // console.log('valeur du slider selon le critère : ', slider.value);
            const value = parseInt(slider.value, 10); // Convertir en entier
            totalScores += value; // Ajouter la valeur au score total

            // Mettre à jour les compteurs basés sur la valeur
            if (value < 1) naCount++;
            else if (value < 2) paCount++;
            else if (value < 3) aCount++;
            else laCount++;
         }
      });
   });


   // Déterminer l'appréciation en fonction des scores obtenus
   if (naCount > 0) {
      result = state.appreciationLabels[0]; // NA - Non acquis
      bgClass = 'bg-error';
   } else if (paCount > 0) {
      result = state.appreciationLabels[1]; // PA - Partiellement acquis
      bgClass = 'bg-warning';
   } else {
      if (aCount > Math.floor(count / 2)) {
         result = state.appreciationLabels[2]; // A - Approuvé
         bgClass = 'bg-success';
      } else {
         result = state.appreciationLabels[3]; // LA - Largement approuvé
         bgClass = 'bg-info';
      }
   }

   // Fonction pour supprimer les classes de couleur de fond existantes
   function removeBackgroundClasses(element) {
      element.classList.forEach(className => {
         if (className.startsWith('bg-')) {
            element.classList.remove(className);
         }
      });
   }

   // Supprimer les classes de couleur de fond existantes
   removeBackgroundClasses(divFinalResult);
   removeBackgroundClasses(divSamllFinalResult);

   // Ajouter la nouvelle classe de couleur de fond
   divFinalResult.classList.add(bgClass);
   divSamllFinalResult.classList.add(bgClass);

   // Afficher le résultat dans les divs
   divFinalResult.querySelector('#finalResultContent').innerHTML = result;
   divFinalResult.classList.replace('hidden', 'flex');
   divSamllFinalResult.querySelector('#smallResultContent').innerHTML = result;
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



window.updateTextareaGeneralRemark = function (textarea, counter) {
   const maxLength = 10000;
   const currentLength = textarea.value.length;
   const remainingCharacters = maxLength - currentLength;
   counter.textContent = remainingCharacters + '/' + maxLength;
}
