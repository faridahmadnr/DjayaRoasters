/**
 * Cart-specific JavaScript functions
 */

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
  initCartPage();
});

/**
 * Initialize cart page functionality
 */
function initCartPage() {
  // Quantity update handlers
  const decreaseBtns = document.querySelectorAll(".decrease-qty");
  const increaseBtns = document.querySelectorAll(".increase-qty");
  const removeBtns = document.querySelectorAll(".remove-btn");

  decreaseBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      const id = this.getAttribute("data-id");
      const qtyInput = document.querySelector(
        `.quantity-input[data-id="${id}"]`
      );
      let quantity = parseInt(qtyInput.value);

      if (quantity > 1) {
        quantity--;
        qtyInput.value = quantity;
        updateCartItem(id, quantity);
      }
    });
  });

  increaseBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      const id = this.getAttribute("data-id");
      const qtyInput = document.querySelector(
        `.quantity-input[data-id="${id}"]`
      );
      let quantity = parseInt(qtyInput.value);
      const maxStock = parseInt(qtyInput.getAttribute("max"));

      if (quantity < maxStock) {
        quantity++;
        qtyInput.value = quantity;
        updateCartItem(id, quantity);
      }
    });
  });

  removeBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      const id = this.getAttribute("data-id");
      if (
        confirm("Are you sure you want to remove this item from your cart?")
      ) {
        removeCartItem(id);
      }
    });
  });
}

/**
 * Update cart item quantity
 */
function updateCartItem(id, quantity) {
  showSpinner(id, true);

  const baseUrl =
    document.querySelector('meta[name="base-url"]')?.getAttribute("content") ||
    "/djaya_roasters";

  fetch(`${baseUrl}/pages/cart/update-cart.php`, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `id=${id}&quantity=${quantity}&action=update`,
  })
    .then((response) => response.json())
    .then((data) => {
      showSpinner(id, false);

      if (data.success) {
        // Update subtotal
        const subtotalElement = document.querySelector(
          `.subtotal[data-id="${id}"]`
        );
        if (subtotalElement) {
          subtotalElement.textContent = `Rp ${formatNumber(
            data.item_subtotal
          )}`;
        }

        // Update cart summary
        const cartSubtotalElement = document.getElementById("cart-subtotal");
        const cartTotalElement = document.getElementById("cart-total");
        if (cartSubtotalElement) {
          cartSubtotalElement.textContent = `Rp ${formatNumber(
            data.cart_total
          )}`;
        }
        if (cartTotalElement) {
          cartTotalElement.textContent = `Rp ${formatNumber(data.cart_total)}`;
        }

        // Update cart count in navbar
        updateCartCount(data.cart_count);

        // Show success message
        showAlert("updateSuccess");

        // Enable/disable buttons based on new quantity
        const decreaseBtn = document.querySelector(
          `.decrease-qty[data-id="${id}"]`
        );
        const increaseBtn = document.querySelector(
          `.increase-qty[data-id="${id}"]`
        );

        if (decreaseBtn) decreaseBtn.disabled = quantity <= 1;
        if (increaseBtn) increaseBtn.disabled = quantity >= data.item_stock;
      } else {
        showAlert("updateError");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showSpinner(id, false);
      showAlert("updateError");
    });
}

/**
 * Remove item from cart
 */
function removeCartItem(id) {
  showSpinner(id, true);

  const baseUrl =
    document.querySelector('meta[name="base-url"]')?.getAttribute("content") ||
    "/djaya_roasters";

  fetch(`${baseUrl}/pages/cart/update-cart.php`, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `id=${id}&action=remove`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Remove row from table
        const row = document.querySelector(`tr[data-id="${id}"]`);
        if (row) row.remove();

        // Update cart summary
        const cartSubtotalElement = document.getElementById("cart-subtotal");
        const cartTotalElement = document.getElementById("cart-total");
        if (cartSubtotalElement) {
          cartSubtotalElement.textContent = `Rp ${formatNumber(
            data.cart_total
          )}`;
        }
        if (cartTotalElement) {
          cartTotalElement.textContent = `Rp ${formatNumber(data.cart_total)}`;
        }

        // Update cart count in navbar
        updateCartCount(data.cart_count);

        // Show success message
        showAlert("updateSuccess");

        // If cart is empty, reload the page to show empty cart message
        if (data.cart_count === 0) {
          setTimeout(() => {
            window.location.reload();
          }, 1000);
        }
      } else {
        showAlert("updateError");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showAlert("updateError");
    });
}

/**
 * Helper function to show spinner
 */
function showSpinner(id, show) {
  const spinner = document.getElementById(`spinner-${id}`);
  if (spinner) {
    spinner.style.display = show ? "inline-block" : "none";
  }
}

/**
 * Helper function to show alert
 */
function showAlert(id) {
  const alert = document.getElementById(id);
  if (alert) {
    alert.style.display = "block";

    setTimeout(() => {
      alert.style.display = "none";
    }, 3000);
  }
}

/**
 * Format number as currency
 */
function formatNumber(num) {
  return new Intl.NumberFormat("id-ID").format(num);
}

/**
 * Update cart count in navbar (duplicate from script.js for standalone use)
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
