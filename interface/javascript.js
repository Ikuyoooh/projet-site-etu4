document.addEventListener("DOMContentLoaded", function () {
    const boutons = document.getElementsByClassName("bouton_menu");

    Array.from(boutons).forEach((btn) => {
        btn.addEventListener("click", function () {
        const cours = btn.nextElementSibling;
        if (cours) {
            cours.classList.toggle("caché");
        }
        });
    });
    const uploadInput = document.getElementById("uploadPDFInput");
    if (uploadInput) {
        uploadInput.addEventListener("change", async function () {
            const file = this.files[0];
            if (!file) return;

            if (file.size > 50 * 1024 * 1024) { // 50 Mo
                alert("Le fichier PDF dépasse la limite de 50 Mo.");
                this.value = '';
                return;
            }

            const formData = new FormData();
            formData.append("pdf", file);

            const response = await fetch(window.location.href, {
                method: "POST",
                body: formData,
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            });

            const result = await response.json();

            if (result.success) {
                const editor = document.getElementById("editor");
                const iframeMarkdown = `<iframe src="${result.url}" width="100%" height="500px"></iframe>\n`;
                editor.value += '\n' + iframeMarkdown;
                renderMarkdown();
            } else {
                alert("Erreur : " + result.message);
            }

            this.value = '';
        });

    }

    document.getElementById('uploadVIDEOInput').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('mp4', file);

        fetch(window.location.href, {
            method: 'POST',
            body: formData,
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const url = data.url;
                const videoMarkdown = `\n<video controls width="100%">\n  <source src="${url}" type="video/mp4">\n  Votre navigateur ne prend pas en charge la lecture de vidéos.\n</video>\n`;
                const textarea = document.getElementById('editor');
                textarea.value += videoMarkdown;
                renderMarkdown();
            } else {
                alert("Erreur : " + (data.message || "Échec de l'upload vidéo."));
            }
        });

        this.value = '';
    });


    if (document.getElementById("editor")) {
        renderMarkdown();
    }
});




function addMarkdown(before, after) {
    const textarea = document.getElementById("editor");
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selected = textarea.value.substring(start, end);
    const newText = before + selected + after;
    textarea.setRangeText(newText, start, end, 'end');
    renderMarkdown();
    textarea.focus();
}

function renderMarkdown() {
    const markdownText = document.getElementById("editor").value;
    document.getElementById("preview").innerHTML = marked.parse(markdownText);
}

function sauvegarderMarkdown() {
    const contenu = document.getElementById('editor').value;
    const nom = document.getElementById('nomCours').value.trim();

    if (!nom) {
        alert("Veuillez saisir un nom de fichier.");
        return;
    }

    fetch("", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
            markdown: contenu,
            nom: nom
        })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message || "Sauvegarde effectuée.");
        if (data.success) {
            window.location.href = "lecture.php?fichier=" + encodeURIComponent(nom + ".md");
        }
    })
    .catch(error => {
        console.error("Erreur:", error);
        alert("Erreur lors de la sauvegarde.");
    });
}
