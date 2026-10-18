async function handleSubmit(event) {
    event.preventDefault();

    const form = event.currentTarget;
    const button = form.querySelector("#login-submit");
    const errorBox = form.querySelector("#login-error");

    errorBox.classList.add("hidden");
    button.disabled = true;
    button.textContent = "Signing in…";

    const payload = new URLSearchParams(new FormData(form));

    try {
        const response = await fetch("/login", {
            method: "POST",
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                ).content,
            },
            body: payload,
        });
        const contentType = response.headers.get("content-type") ?? "";
        const result = contentType.includes("application/json")
            ? await response.json()
            : null;

        if (!response.ok) {
            throw new Error(
                result?.message ??
                    (response.status >= 500
                        ? "The sign-in service is temporarily unavailable. Please try again."
                        : "Unable to sign in."),
            );
        }

        window.location.href = result.redirect ?? "/dashboard";
    } catch (error) {
        errorBox.textContent = error.message;
        errorBox.classList.remove("hidden");
        button.disabled = false;
        button.textContent = "Sign in";
    }
}

export const loginPage = {
    init() {
        const form = document.querySelector("#login-form");
        if (form) form.addEventListener("submit", handleSubmit);
    },
};
