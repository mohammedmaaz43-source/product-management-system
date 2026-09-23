
// ===============================
// DELETE CONFIRMATION
// ===============================

const deleteButtons =
    document.querySelectorAll(
        ".btn-delete"
    );

deleteButtons.forEach(
    function (button) {

        button.addEventListener(
            "click",
            function (event) {

                const confirmDelete =
                    confirm(
                        "Are you sure you want to delete this item?"
                    );

                if (!confirmDelete) {

                    event.preventDefault();

                }

            }
        );

    }
);


// ===============================
// AUTO HIDE MESSAGES
// ===============================

const messages =
    document.querySelectorAll(
        ".success-message, .error-message"
    );

messages.forEach(
    function (message) {

        setTimeout(
            function () {

                message.style.display =
                    "none";

            },
            4000
        );

    }
);


// ===============================
// IMAGE PREVIEW
// ===============================

const imageInput =
    document.querySelector(
        "#productImage"
    );

const imagePreview =
    document.querySelector(
        "#imagePreview"
    );

if (imageInput && imagePreview) {

    imageInput.addEventListener(
        "change",
        function () {

            const file =
                imageInput.files[0];

            if (file) {

                const reader =
                    new FileReader();

                reader.onload =
                    function (event) {

                        imagePreview.src =
                            event.target.result;

                        imagePreview.style.display =
                            "block";

                    };

                reader.readAsDataURL(
                    file
                );

            }

        }
    );

}


// ===============================
// MOBILE SIDEBAR
// ===============================

const menuButton =
    document.querySelector(
        ".menu-button"
    );

const sidebar =
    document.querySelector(
        ".sidebar"
    );

if (menuButton && sidebar) {

    menuButton.addEventListener(
        "click",
        function () {

            sidebar.classList.toggle(
                "show-sidebar"
            );

        }
    );

}


// ===============================
// CLOSE ALERT MESSAGE
// ===============================

const closeButtons =
    document.querySelectorAll(
        ".close-message"
    );

closeButtons.forEach(
    function (button) {

        button.addEventListener(
            "click",
            function () {

                const message =
                    button.parentElement;

                message.style.display =
                    "none";

            }
        );

    }
);


// ===============================
// DARK / LIGHT THEME
// ===============================

const themeToggle =
    document.getElementById(
        "themeToggle"
    );

const body =
    document.body;


if (themeToggle) {

    const savedTheme =
        localStorage.getItem(
            "theme"
        );


    if (savedTheme === "light") {

        body.classList.remove(
            "dark-theme"
        );

        body.classList.add(
            "light-theme"
        );

        themeToggle.innerHTML =
            "🌙 Dark Mode";

    } else {

        body.classList.remove(
            "light-theme"
        );

        body.classList.add(
            "dark-theme"
        );

        themeToggle.innerHTML =
            "☀️ Light Mode";

    }


    themeToggle.addEventListener(
        "click",
        function () {

            if (
                body.classList.contains(
                    "light-theme"
                )
            ) {

                body.classList.remove(
                    "light-theme"
                );

                body.classList.add(
                    "dark-theme"
                );

                localStorage.setItem(
                    "theme",
                    "dark"
                );

                themeToggle.innerHTML =
                    "☀️ Light Mode";

            } else {

                body.classList.remove(
                    "dark-theme"
                );

                body.classList.add(
                    "light-theme"
                );

                localStorage.setItem(
                    "theme",
                    "light"
                );

                themeToggle.innerHTML =
                    "🌙 Dark Mode";

            }

        }
    );

}

// ===============================
// PASSWORD SHOW / HIDE
// ===============================

function togglePassword() {

    const password =
        document.getElementById(
            "password"
        );

    if (!password) {

        return;

    }

    if (password.type === "password") {

        password.type =
            "text";

    } else {

        password.type =
            "password";

    }

}

// ===============================
// CATEGORY ADD ANIMATION
// ===============================

const categoryOverlay =
    document.getElementById(
        "categorySuccessOverlay"
    );

if (categoryOverlay) {

    const stepOne =
        document.getElementById(
            "categoryStepOne"
        );

    const stepTwo =
        document.getElementById(
            "categoryStepTwo"
        );

    const stepThree =
        document.getElementById(
            "categoryStepThree"
        );

    const progressText =
        document.getElementById(
            "categoryProgress"
        );


    setTimeout(function () {

        if (stepOne) {
            stepOne.style.display =
                "none";
        }

        if (stepTwo) {
            stepTwo.style.display =
                "block";
        }


        let progress = 0;

        const progressTimer =
            setInterval(function () {

                progress += 5;

                if (progressText) {

                    progressText.textContent =
                        progress + "%";

                    const ring =
                        stepTwo.querySelector(
                            ".action-progress-ring"
                        );

                    if (ring) {

                        ring.style.background =
                            `conic-gradient(
                                #7c6cff ${progress * 3.6}deg,
                                rgba(148,163,184,0.12)
                                ${progress * 3.6}deg
                            )`;
                    }
                }


                if (progress >= 100) {

                    clearInterval(
                        progressTimer
                    );


                    setTimeout(function () {

                        if (stepTwo) {
                            stepTwo.style.display =
                                "none";
                        }

                        if (stepThree) {
                            stepThree.style.display =
                                "block";
                        }


                        setTimeout(function () {

                            window.location.href =
                                "categories.php";

                        }, 1800);

                    }, 300);
                }

            }, 45);

    }, 900);
}



// ===============================
// SUPPLIER ADD ANIMATION
// ===============================

const supplierOverlay =
    document.getElementById(
        "supplierSuccessOverlay"
    );

if (supplierOverlay) {

    const stepOne =
        document.getElementById(
            "supplierStepOne"
        );

    const stepTwo =
        document.getElementById(
            "supplierStepTwo"
        );

    const stepThree =
        document.getElementById(
            "supplierStepThree"
        );

    const progressText =
        document.getElementById(
            "supplierProgress"
        );


    setTimeout(function () {

        if (stepOne) {
            stepOne.style.display =
                "none";
        }

        if (stepTwo) {
            stepTwo.style.display =
                "block";
        }


        let progress = 0;

        const progressTimer =
            setInterval(function () {

                progress += 5;

                if (progressText) {

                    progressText.textContent =
                        progress + "%";

                    const ring =
                        stepTwo.querySelector(
                            ".action-progress-ring"
                        );

                    if (ring) {

                        ring.style.background =
                            `conic-gradient(
                                #3b82f6 ${progress * 3.6}deg,
                                rgba(148,163,184,0.12)
                                ${progress * 3.6}deg
                            )`;
                    }
                }


                if (progress >= 100) {

                    clearInterval(
                        progressTimer
                    );


                    setTimeout(function () {

                        if (stepTwo) {
                            stepTwo.style.display =
                                "none";
                        }

                        if (stepThree) {
                            stepThree.style.display =
                                "block";
                        }


                        setTimeout(function () {

                            window.location.href =
                                "suppliers.php";

                        }, 1800);

                    }, 300);
                }

            }, 45);

    }, 900);
}

// =========================================
// DASHBOARD LOADER
// =========================================

const dashboardLoadingScreen =
    document.getElementById(
        "dashboardLoadingScreen"
    );


if (dashboardLoadingScreen) {

    const dashboardProgress =
        document.getElementById(
            "dashboardLoaderProgress"
        );


    const dashboardPercentage =
        document.getElementById(
            "dashboardLoaderPercentage"
        );


    let dashboardPercent = 0;


    const dashboardLoaderInterval =
        setInterval(
            function () {

                dashboardPercent++;


                if (dashboardProgress) {

                    dashboardProgress.style.width =
                        dashboardPercent + "%";

                }


                if (dashboardPercentage) {

                    dashboardPercentage.innerHTML =
                        dashboardPercent + "%";

                }


                if (dashboardPercent >= 100) {

                    clearInterval(
                        dashboardLoaderInterval
                    );


                    setTimeout(
                        function () {

                            dashboardLoadingScreen.style.opacity =
                                "0";


                            dashboardLoadingScreen.style.visibility =
                                "hidden";


                            setTimeout(
                                function () {

                                    dashboardLoadingScreen.remove();

                                },
                                500
                            );

                        },
                        300
                    );

                }

            },
            15
        );

}