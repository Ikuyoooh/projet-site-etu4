let questionCount = 0;


function ajouterQuestion() {
    questionCount++;
    
    const container = document.getElementById('questionsContainer');
    
    const questionBlock = document.createElement('div');
    questionBlock.className = 'question-block';
    questionBlock.id = `question-${questionCount}`;
    
    questionBlock.innerHTML = `
        <h3>Question ${questionCount}</h3>
        
        <label>Question *</label><br>
        <textarea class="question-text" rows="3" style="width: 60%; padding: 8px; border: 1px solid #ccc; border-radius: 2px;" 
                  placeholder="Ex : est ce que ça fonctionne ?" required></textarea>
        <br><br>
        
        <label>Réponses possibles (cochez la bonne réponse) :</label><br>
        
        <div class="choix-group">
            <input type="radio" name="reponse-${questionCount}" value="1" checked>
            <input type="text" class="choix-1" placeholder="Réponse 1" required>
        </div>
        
        <div class="choix-group">
            <input type="radio" name="reponse-${questionCount}" value="2">
            <input type="text" class="choix-2" placeholder="Réponse 2" required>
        </div>
        
        <div class="choix-group">
            <input type="radio" name="reponse-${questionCount}" value="3">
            <input type="text" class="choix-3" placeholder="Réponse 3 (optionnelle)">
        </div>
        
        <div class="choix-group">
            <input type="radio" name="reponse-${questionCount}" value="4">
            <input type="text" class="choix-4" placeholder="Réponse 4 (optionnelle)">
        </div>
        
        <br>
        <button type="button" class="btn-remove-question" onclick="supprimerQuestion('question-${questionCount}')">
            Supprimer cette question
        </button>
    `;
    
    container.appendChild(questionBlock);
}


function supprimerQuestion(questionId) {
    if (confirm('Voulez-vous vraiment supprimer cette question ?')) {
        document.getElementById(questionId).remove();
    }
}


function collecterQuestions() {
    const questions = [];
    const questionBlocks = document.querySelectorAll('.question-block');
    
    questionBlocks.forEach((block, index) => {
        const questionText = block.querySelector('.question-text').value.trim();
        const choix1 = block.querySelector('.choix-1').value.trim();
        const choix2 = block.querySelector('.choix-2').value.trim();
        const choix3 = block.querySelector('.choix-3').value.trim();
        const choix4 = block.querySelector('.choix-4').value.trim();
        
        const bonneReponse = block.querySelector('input[type="radio"]:checked').value;
        
        if (!questionText || !choix1 || !choix2) {
            throw new Error(`Question ${index + 1} : La question et au moins 2 réponses sont obligatoires.`);
        }
        
        questions.push({
            question: questionText,
            choix_1: choix1,
            choix_2: choix2,
            choix_3: choix3,
            choix_4: choix4,
            bonne_reponse: bonneReponse
        });
    });
    
    return questions;
}


document.getElementById('formQCM').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const titre = document.getElementById('titreQCM').value.trim();
    const temps = document.getElementById('tempsQCM').value;
    const matiere = document.getElementById('matiereQCM').value;
    const module = document.getElementById('moduleQCM').value;
    const cours = document.getElementById('coursQCM').value;
    
    console.log('Données générales:', { titre, temps, matiere, module, cours });
    
    
    if (!titre || !temps || !matiere || !module || !cours) {
        alert('Tous les champs généraux sont obligatoires.');
        return;
    }
    
    
    let questions;
    try {
        questions = collecterQuestions();
    } catch (error) {
        alert(error.message);
        return;
    }
    
    if (questions.length === 0) {
        alert('Vous devez ajouter au moins une question.');
        return;
    }
    
    console.log('Questions collectées:', questions);
    
    
    const formData = new FormData();
    formData.append('action', 'generer_qcm');
    formData.append('titre', titre);
    formData.append('temps', temps);
    formData.append('cours', cours);
    formData.append('module', module);
    formData.append('questions', JSON.stringify(questions));
    
    console.log('Envoi vers qcm.php...');
    
    
    fetch('qcm.php', {
        method: 'POST',
        body: formData
    })
    .then(r => {
        console.log('Réponse HTTP:', r.status, r.statusText);
        return r.json();
    })
    .then(data => {
        console.log('Données reçues:', data);
        
        if (!data.success) {
            alert('Erreur : ' + data.message);
            if (data.details) {
                console.error('Détails:', data.details);
            }
            return;
        }
        
        
        showPopup(data.message, 'success');
        
        
        setTimeout(() => {
            window.location.href = 'liste_qcm.php';
        }, 2000);
    })
    .catch(error => {
        console.error('Erreur complète:', error);
        alert('Erreur lors de la création du QCM : ' + error.message);
    });
});


function showPopup(message, type = 'success') {
    const overlay = document.getElementById('overlay');
    const popup = document.getElementById('popup');
    const msg = document.getElementById('popupMessage');
    
    popup.className = `popup ${type}`;
    msg.textContent = message;
    
    overlay.classList.remove('hidden');
    popup.classList.remove('hidden');
}


function fermerPopup() {
    document.getElementById('overlay').classList.add('hidden');
    document.getElementById('popup').classList.add('hidden');
}


window.addEventListener('DOMContentLoaded', function() {
    ajouterQuestion();
});
