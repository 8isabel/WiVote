

const menuButton = document.querySelector(".menu-button");
const footer = document.querySelector(".bottom");

if (menuButton && footer) {
    menuButton.addEventListener("click", () => {
        footer.classList.toggle("closed");
        menuButton.classList.toggle("closed");
    });
}
