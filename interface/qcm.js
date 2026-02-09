function genererQCM() {

    const formData = new FormData();
    formData.append('action', 'generer_qcm');
    formData.append('titre', document.getElementById('titreQCM').value);
    formData.append('temps', document.getElementById('tempsQCM').value);
    formData.append('cours', document.getElementById('coursQCM').value);
    formData.append('module', document.getElementById('moduleQCM').value);

    fetch('generer_qcm.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            alert(data.message);
            return;
        }
        afficherQCM(data.qcm);
    });
}

function afficherQCM(qcm) {

    let html = `
        <h3>${qcm.titre}</h3>
        <p><strong>Temps :</strong> ${qcm.temps} min</p>
        <p><strong>Cours :</strong> ${qcm.cours} | <strong>Module :</strong> ${qcm.module}</p>
        <hr>
    `;

    qcm.questions.forEach((q, i) => {
        html += `<div class="question">
            <p><strong>Q${i+1}.</strong> ${q.question}</p>`;

        q.choix.forEach((choix, index) => {
            html += `
                <label>
                    <input type="radio" name="q${i}">
                    ${choix}
                </label><br>
            `;
        });

        html += `</div><br>`;
    });

    document.getElementById('resultatQCM').innerHTML = html;


function showPopup(message, type = 'success') {
        const popup = document.getElementById('popup');
        const msg = document.getElementById('popupMessage');
    
        popup.className = 'popup ' + type;
        msg.textContent = message;
    
        popup.classList.remove('hidden');
    }
    
    function fermerPopup() {
        document.getElementById('popup').classList.add('hidden');
    }
    
}
