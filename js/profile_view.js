document.querySelectorAll(".selectbox").forEach(selectbox => {
    selectbox.addEventListener("click", () => {
        document.querySelector(".selected")?.classList.remove("selected");
        selectbox.classList.add("selected");
    });
});
