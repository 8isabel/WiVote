document.querySelectorAll(".selectbox").forEach(selectbox => {
    selectbox.addEventListener("click", () => {
        document.querySelector(".selected")?.classList.remove("selected");
        selectbox.classList.add("selected");
    });
});


const menuButton = document.querySelector(".menu-button");
const footer = document.querySelector(".bottom");

menuButton.addEventListener("click", () => {
    footer.classList.toggle("closed");
    menuButton.classList.toggle("closed");
});
