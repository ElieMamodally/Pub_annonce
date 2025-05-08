// Ajouter la publication au fil d'actualité
function addPostToFeed(post) {
    const feed = document.querySelector(".post-container");
    const postDiv = document.createElement("div");
    postDiv.innerHTML = `<strong>${post.username}</strong>: ${post.content}`;
    feed.prepend(postDiv);
}


function toggleReplyForm(commentId) {
    const replyForm = document.getElementById(`reply-form-${commentId}`);
    if (replyForm.style.display === "none" || !replyForm.style.display) {
        replyForm.style.display = "block";
    } else {
        replyForm.style.display = "none";
    }
}

//Mise à jour PDP PDC------------------------------
    document.addEventListener("DOMContentLoaded", function() {
        const pdpInput = document.getElementById('pdp');
        const pdcInput = document.getElementById('pdc');
    
        if (pdpInput) {
            pdpInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('pdpselected').src = e.target.result;
                        document.getElementById('nameImgPdpUploaded').textContent = this.files[0].name;
                    }.bind(this);
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
    
        if (pdcInput) {
            pdcInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('pdcselected').src = e.target.result;
                        document.getElementById('nameImgPdcUploaded').textContent = this.files[0].name;
                    }.bind(this);
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
    });
    



function updateNotificationCount() {
    fetch('get_notification_count.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('notification-count');
            if (badge) {
                badge.textContent = data.count;
                badge.style.display = data.count > 0 ? 'inline-block' : 'none';
            }
        })
        .catch(error => console.error('Erreur lors de la récupération des notifications :', error));
}

// Mettre à jour toutes les 1 secondes
setInterval(updateNotificationCount, 1000);

// Mettre à jour immédiatement au chargement de la page
updateNotificationCount();

$(document).on("submit", ".comment-form", function (e) {
    e.preventDefault();

    const form = $(this);
    const postId = form.find("input[name='post_id']").val();
    const comment = form.find("input[name='comment']").val();

    $.ajax({
        url: "add_comment.php",
        type: "POST",
        data: { post_id: postId, comment: comment, parent_id: 0 },
        success: function (response) {
            // Rechargez la section des commentaires
            $(".comments").load(location.href + " .comments > *");
            form.find("input[name='comment']").val(""); // Réinitialisez le champ de texte
        },
        error: function (xhr, status, error) {
            console.error("Erreur lors de l'ajout du commentaire :", error);
        },
    });
});



