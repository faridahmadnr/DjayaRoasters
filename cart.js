/**
 * Cart functionality for Djaya Roasters
 */

class DjayaCart {
  constructor(baseUrl) {
    this.baseUrl = baseUrl;
    this.cartCount = 0;
    this.initCart();
  }

  /**
   * Initialize cart
   */
  initCart() {
    // Get cart count from badge
    const cartBadge = document.getElementById("cartCount");
    if (cartBadge) {
      this.cartCount = parseInt(cartBadge.textContent) || 0;
    }

    // Initialize add to cart forms
    this.initAddToCartForms();
  }

  /**
   * Initialize all add to cart forms on the page
   */
  initAddToCartForms() {
    const addToCartForms = document.querySelectorAll(
      'form[data-cart-action="add"]'
    );

    addToCartForms.forEach((form) => {
      form.addEventListener("submit", (e) => {
        e.preventDefault();
        this.addToCart(form);
      });
    });
  }

  /**
   * Add a product to the cart
   */
  addToCart(form) {
    const formData = new FormData(form);

    // Show loading indicator if available
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
      const originalText = submitBtn.innerHTML;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
      submitBtn.disabled = true;
    }

    fetch(`${this.baseUrl}/pages/cart/add-to-cart.php`, {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        // Reset button
        if (submitBtn) {
          submitBtn.innerHTML = originalText;
          submitBtn.disabled = false;
        }

        if (data.success) {
          // Update cart count
          this.updateCartCount(data.cart_count);

          // Show success message
          this.showToast("Product added to cart successfully!", "success");
        } else {
          this.showToast("Failed to add product to cart", "error");
        }
      })
      .catch((error) => {
        console.error("Error:", error);

        // Reset button
        if (submitBtn) {
          submitBtn.innerHTML = originalText;
          submitBtn.disabled = false;
        }

        this.showToast("An error occurred. Please try again.", "error");
      });
  }

  /**
   * Update the cart count in the UI
   */
  updateCartCount(count) {
    this.cartCount = count;
    const cartBadge = document.getElementById("cartCount");

    if (cartBadge) {
      cartBadge.textContent = count;

      if (count > 0) {
        cartBadge.style.display = "inline-flex";
      } else {
        cartBadge.style.display = "none";
      }
    }
  }

  /**
   * Show a toast notification
   */
  showToast(message, type = "success") {
    // Check if toastContainer exists
    let toastContainer = document.querySelector(".toast-container");

    // Create it if it doesn't exist
    if (!toastContainer) {
      toastContainer = document.createElement("div");
      toastContainer.className = "toast-container";
      document.body.appendChild(toastContainer);
    }

    const toastId = "toast-" + Date.now();
    const toast = document.createElement("div");
    toast.id = toastId;
    toast.className = `toast ${
      type === "success" ? "toast-success" : "toast-error"
    }`;
    toast.setAttribute("role", "alert");
    toast.setAttribute("aria-live", "assertive");
    toast.setAttribute("aria-atomic", "true");

    toast.innerHTML = `
            <div class="toast-header">
                <strong class="me-auto">${
                  type === "success" ? "Success" : "Error"
                }</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">${message}</div>
        `;

    toastContainer.appendChild(toast);

    const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
    bsToast.show();

    // Remove toast after it's hidden
    toast.addEventListener("hidden.bs.toast", function () {
      toast.remove();
    });
  }
}

// Initialize cart when the DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  // Get the base URL from a meta tag or a global variable
  const baseUrl =
    document.querySelector('meta[name="base-url"]')?.getAttribute("content") ||
    "/djaya_roasters";
  window.djayaCart = new DjayaCart(baseUrl);
});
