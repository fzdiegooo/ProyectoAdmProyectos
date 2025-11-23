document.addEventListener("DOMContentLoaded", function () {
    let chatbotButton = document.querySelector(".chatbot-button");
    let chatbotContainer = document.querySelector(".chatbot-container");
    let carousel = document.querySelector(".carousel-container"); // Carrusel

    if (!chatbotButton || !chatbotContainer || !carousel) return; // Evita errores si algún elemento no existe

    // Intersection Observer para detectar el carrusel
    let observer = new IntersectionObserver(
        function (entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    chatbotButton.classList.add("fadeOut");
                    chatbotContainer.classList.add("fadeOut");
                } else {
                    chatbotButton.classList.remove("fadeOut");
                    chatbotContainer.classList.remove("fadeOut");
                }
            });
        },
        { threshold: 0.3 } // Se activa cuando al menos el 30% del carrusel es visible
    );

    observer.observe(carousel);
});