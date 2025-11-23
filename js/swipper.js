var swiper = new Swiper(".mySwiper", {
    slidesPerView: 1, // Mostrar más productos en escritorio
    spaceBetween: 10,
    loop: true,
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    },
    breakpoints: {
        670: {
            slidesPerView: 2, // 2 productos en pantallas medianas
            spaceBetween: 10,
        },
        1000: {
            slidesPerView: 3, // 3 productos en pantallas grandes
            spaceBetween: 15,
        }
    }
});

document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".product-card").forEach(card => {
        card.addEventListener("click", function() {
            window.location.href = this.getAttribute("data-url");
        });
    });

    document.querySelectorAll(".product-button").forEach(button => {
        button.addEventListener("click", function(event) {
            event.stopPropagation(); // Evita que el clic en el botón active el clic de la tarjeta
            window.location.href = this.getAttribute("data-url");
        });
    });
});

/*
function toggleZoom(element) {
    let img = element.querySelector("img");
    element.classList.toggle("zoomed");

    if (!element.classList.contains("zoomed")) {
        img.style.transform = "scale(1) translate(0, 0)";
    }
}

function moveZoom(event, element) {
    if (!element.classList.contains("zoomed")) return;

    let img = element.querySelector("img");
    let rect = element.getBoundingClientRect();
    
    let offsetX = ((event.clientX - rect.left) / rect.width) * 100;
    let offsetY = ((event.clientY - rect.top) / rect.height) * 100;

    img.style.transformOrigin = `${offsetX}% ${offsetY}%`;
    img.style.transform = "scale(2)";
}
    */