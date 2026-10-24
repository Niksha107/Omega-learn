document.addEventListener("DOMContentLoaded", function () {
    const pageName = window.location.pathname.split("/").pop() || "index.html";

    document.querySelectorAll("nav a").forEach(function (link) {
        if (link.getAttribute("href") === pageName) {
            link.classList.add("active");
        }
    });

    document.querySelectorAll(".search input").forEach(function (searchInput) {
        searchInput.addEventListener("input", function () {
            const value = searchInput.value.toLowerCase().trim();

            document.querySelectorAll(".course-card, .product").forEach(function (item) {
                item.style.display =
                    item.textContent.toLowerCase().includes(value) ? "" : "none";
            });
        });
    });

    const signupForm = document.getElementById("signupForm");
    if (signupForm) {
        signupForm.addEventListener("submit", function (event) {
            let valid = true;
            const value = function (id) {
                return document.getElementById(id).value.trim();
            };
            const showError = function (id, message) {
                document.getElementById(id + "Error").textContent = message;
                valid = false;
            };

            document.querySelectorAll(".error").forEach(function (error) {
                error.textContent = "";
            });

            if (!/^[A-Za-z ]{3,}$/.test(value("name"))) {
                showError("name", "Please enter a valid name.");
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value("email"))) {
                showError("email", "Please enter a valid email.");
            }
            if (!/^[a-zA-Z0-9_]{3,30}$/.test(value("username"))) {
                showError("username", "Username must be 3-30 letters, numbers or underscores.");
            }
            if (!/^[6-9]\d{9}$/.test(value("phone"))) {
                showError("phone", "Enter a valid 10-digit mobile number.");
            }
            if (value("address").length < 5) {
                showError("address", "Please enter a valid address.");
            }
            ["city", "state", "course", "year"].forEach(function (id) {
                if (!value(id)) {
                    showError(id, "Please make a selection.");
                }
            });
            if (!/^\d{6}$/.test(value("pincode"))) {
                showError("pincode", "Pincode must contain 6 digits.");
            }
            if (!document.querySelector('input[name="gender"]:checked')) {
                showError("gender", "Please select your gender.");
            }

            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirmPassword").value;
            if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/.test(password)) {
                showError("password", "Password needs 8 characters, uppercase, lowercase, number and special character.");
            }
            if (password !== confirmPassword) {
                showError("confirmPassword", "Passwords do not match.");
            }
            if (!document.getElementById("terms").checked) {
                document.getElementById("termsError").textContent =
                    "You must accept the Terms & Conditions.";
                valid = false;
            }

            if (!valid) {
                event.preventDefault();
            }
        });

        const passwordField = document.getElementById("password");
        const strength = document.getElementById("strength");
        if (passwordField && strength) {
            passwordField.addEventListener("input", function () {
                const length = passwordField.value.length;
                strength.textContent = length === 0 ? "" :
                    length < 6 ? "Weak password" :
                    length < 8 ? "Medium password" : "Strong password";
                strength.style.color = length < 6 ? "#d93025" :
                    length < 8 ? "#f39c12" : "#2e7d32";
            });
        }
    }

    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
        loginForm.addEventListener("submit", function (event) {
            const email = document.getElementById("loginEmail");
            const password = document.getElementById("loginPassword");
            const emailError = document.getElementById("loginEmailError");
            const passwordError = document.getElementById("loginPasswordError");

            emailError.textContent = email.value.trim() ? "" : "Please enter your email.";
            passwordError.textContent = password.value ? "" : "Please enter your password.";

            if (!email.value.trim() || !password.value) {
                event.preventDefault();
            }
        });
    }

    document.querySelectorAll(".google").forEach(function (button) {
        button.addEventListener("click", function (event) {
            event.preventDefault();
            alert("Google Login will be available soon.");
        });
    });

    const noticeList = document.getElementById("noticeList");
    if (!noticeList) {
        return;
    }

    const searchNotice = document.getElementById("searchNotice");
    const categoryFilter = document.getElementById("categoryFilter");
    const sortNotice = document.getElementById("sortNotice");
    const loading = document.getElementById("loading");
    const errorMessage = document.getElementById("errorMessage");
    const pagination = document.getElementById("pagination");
    const noticesPerPage = 5;
    let notices = [];
    let currentPage = 1;

    fetch("notice_api.php", { headers: { Accept: "application/json" } })
        .then(function (response) {
            if (!response.ok) {
                throw new Error("Unable to load notices.");
            }
            return response.json();
        })
        .then(function (data) {
            if (!data.success || !Array.isArray(data.data)) {
                throw new Error(data.message || "Invalid notices response.");
            }
            notices = data.data;
            loading.hidden = true;
            renderNotices();
        })
        .catch(function (error) {
            loading.hidden = true;
            errorMessage.textContent = "Error loading notices. Please try again later.";
            console.error(error);
        });

    function renderNotices() {
        const searchValue = searchNotice.value.toLowerCase().trim();
        let filtered = notices.filter(function (notice) {
            const matchesSearch = (notice.title + " " + notice.description)
                .toLowerCase().includes(searchValue);
            const matchesCategory = categoryFilter.value === "All" ||
                notice.category === categoryFilter.value;
            return matchesSearch && matchesCategory;
        });

        filtered.sort(function (a, b) {
            return sortNotice.value === "newest"
                ? b.date.localeCompare(a.date)
                : a.date.localeCompare(b.date);
        });

        const totalPages = Math.ceil(filtered.length / noticesPerPage);
        currentPage = Math.min(currentPage, Math.max(totalPages, 1));
        const pageNotices = filtered.slice(
            (currentPage - 1) * noticesPerPage,
            currentPage * noticesPerPage
        );
        noticeList.replaceChildren();

        pageNotices.forEach(function (notice) {
            const card = document.createElement("article");
            card.className = "notice-card";

            const title = document.createElement("h3");
            title.textContent = notice.title;

            const category = document.createElement("p");
            category.className = "notice-category";
            category.textContent = notice.category;

            const date = document.createElement("p");
            date.className = "notice-date";
            date.textContent = notice.date;

            const description = document.createElement("p");
            description.textContent = notice.description;

            card.append(title, category, date, description);
            noticeList.appendChild(card);
        });

        if (filtered.length === 0) {
            loading.hidden = false;
            loading.textContent = "No notices found.";
        } else {
            loading.hidden = true;
            loading.textContent = "";
        }

        renderPagination(totalPages);
    }

    function renderPagination(totalPages) {
        pagination.replaceChildren();

        if (totalPages > 1) {
            const previous = document.createElement("button");
            previous.type = "button";
            previous.textContent = "Previous";
            previous.disabled = currentPage === 1;
            previous.addEventListener("click", function () {
                if (currentPage > 1) {
                    currentPage--;
                    renderNotices();
                }
            });
            pagination.appendChild(previous);
        }

        for (let page = 1; page <= totalPages; page++) {
            const button = document.createElement("button");
            button.type = "button";
            button.textContent = page;
            if (page === currentPage) {
                button.classList.add("active");
                button.setAttribute("aria-current", "page");
            }
            button.addEventListener("click", function () {
                currentPage = page;
                renderNotices();
            });
            pagination.appendChild(button);
        }

        if (totalPages > 1) {
            const next = document.createElement("button");
            next.type = "button";
            next.textContent = "Next";
            next.disabled = currentPage === totalPages;
            next.addEventListener("click", function () {
                if (currentPage < totalPages) {
                    currentPage++;
                    renderNotices();
                }
            });
            pagination.appendChild(next);
        }
    }

    searchNotice.addEventListener("input", function () {
        currentPage = 1;
        renderNotices();
    });
    categoryFilter.addEventListener("change", function () {
        currentPage = 1;
        renderNotices();
    });
    sortNotice.addEventListener("change", function () {
        currentPage = 1;
        renderNotices();
    });
});
