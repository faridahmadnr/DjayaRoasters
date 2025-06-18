/**
 * Djaya Roasters main JavaScript file
 */

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
  // Initialize cart functionality
  initCart();
});

/**
 * Initialize cart functionality
 */
function initCart() {
  // Add to cart form handling
  const addToCartForms = document.querySelectorAll(
    'form[data-action="add-to-cart"]'
  );

  addToCartForms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      e.preventDefault();

      const formData = new FormData(this);
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalBtnText = submitBtn.innerHTML;

      // Disable button and show loading state
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';

      // Send AJAX request
      fetch(form.getAttribute("action"), {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          // Reset button state
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalBtnText;

          if (data.success) {
            // Update cart count in navbar
            updateCartCount(data.cart_count);

            // Show success message
            showToast("Product added to cart successfully!", "success");
          } else {
            if (data.redirect) {
              // Needs login, redirect
              window.location.href = data.redirect;
            } else {
              // Show error message
              showToast(
                data.message || "Failed to add product to cart",
                "error"
              );
            }
          }
        })
        .catch((error) => {
          // Reset button state
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalBtnText;

          // Show error message
          showToast("An error occurred. Please try again.", "error");
          console.error("Error:", error);
        });
    });
  });
}

/**
 * Update cart count in the navbar
 */
function updateCartCount(count) {
  const cartCountElement = document.getElementById("cartCount");
  if (cartCountElement) {
    cartCountElement.textContent = count;

    if (count > 0) {
      cartCountElement.style.display = "inline-flex";
    } else {
      cartCountElement.style.display = "none";
    }
  }
}

/**
 * Show toast message
 */
function showToast(message, type = "success") {
  // Check if toast container exists
  let toastContainer = document.querySelector(".toast-container");

  // Create toast container if it doesn't exist
  if (!toastContainer) {
    toastContainer = document.createElement("div");
    toastContainer.className = "toast-container";
    document.body.appendChild(toastContainer);
  }

  // Create a unique ID for the toast
  const toastId = "toast-" + Date.now();

  // Create toast element
  const toastHtml = `
        <div id="${toastId}" class="toast ${
    type === "success" ? "toast-success" : "toast-error"
  }" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">${
                  type === "success" ? "Success" : "Error"
                }</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;

  // Add to container
  toastContainer.insertAdjacentHTML("beforeend", toastHtml);

  // Initialize Bootstrap toast
  const toastElement = document.getElementById(toastId);
  const bsToast = new bootstrap.Toast(toastElement, { delay: 3000 });
  bsToast.show();

  // Remove when hidden
  toastElement.addEventListener("hidden.bs.toast", function () {
    toastElement.remove();
  });
}
