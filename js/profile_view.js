// Collapsible bottom menu bar. Tab switching for the avatar category boxes
// (.selectbox) is handled in js/avatar.js, since it also has to rebuild the
// Mii options panel for the newly selected category.

const menuButton = document.querySelector(".menu-button");
const footer = document.querySelector(".bottom");

if (menuButton && footer) {
    menuButton.addEventListener("click", () => {
        footer.classList.toggle("closed");
        menuButton.classList.toggle("closed");
    });
}
