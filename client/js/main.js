const T_CDN_URL = "https://tipzty-cdn.onrender.com";

// brands configurations
const tConfigurations = {
  "tonymolycolombia.com": {
    position: "right",
  },
  "dluchi.com": {
    position: "left",
  },
};

let tConfiguration = {};
let videoTextConstant = "Video 👀";

jQuery(document).ready(() => {
  console.log("Tipzty is a live! 🔥 v1.9.2");

  tConfiguration = getBrandConfiguration();

  const classPosition = getClassWidgetPosition(tConfiguration);

  setTimeout(() => {
    const review = jQuery("t-review");
    const block = jQuery("t-block");
    const blockPlay = jQuery("t-block-play");
    const blockPreview = jQuery("t-block-preview");

    if (review) {
      review.removeClass("t-right-16");
      review.removeClass("t-left-16");
      review.addClass(classPosition);
    }

    if (block) {
      block.removeClass("t-right-16");
      block.removeClass("t-left-16");
      block.addClass(classPosition);
    }

    if (blockPlay) {
      blockPlay.removeClass("t-right-16");
      blockPlay.removeClass("t-left-16");
      blockPlay.addClass(classPosition);
    }

    if (blockPreview) {
      blockPreview.removeClass("t-right-16");
      blockPreview.removeClass("t-left-16");
      blockPreview.addClass(classPosition);
    }
  }, 2000);

  tCheckCheckoutAndOrders();
  tCheckMicroSiteEmbed();
  tCheckReviewWidgetInMobile();
});

const tCheckReviewWidgetInMobile = () => {
  if (window.innerWidth < 769) {
    const $overlay = jQuery(".t-overlay");
    const $review = jQuery(".t-review");

    $overlay.click(() => {
      $review.toggleClass("t-size-md");
    });
  }
};

const tCheckCheckoutAndOrders = () => {
  const isCheckoutPage =
    window.location.pathname.startsWith("/checkout") ||
    !!document.body.classList.contains("woocommerce-checkout");
  const isCheckoutOrderReceivedPage =
    window.location.pathname.startsWith("/checkout/order-received/") ||
    (!!document.body.classList.contains("woocommerce-checkout") &&
      !!document.body.classList.contains("woocommerce-order-received"));
  const search = window.location.search;
  const params = new URLSearchParams(search);
  const isFromTipztv = params.get("from") === "tipztv";

  let isValid = true;

  if (isCheckoutOrderReceivedPage) {
    console.log("✅ Checkout order received");

    const emailElement = document.querySelector(
      ".woocommerce-order-overview__email strong"
    );
    const totalAmountElement = document.querySelector(
      ".woocommerce-Price-amount bdi"
    );
    const orderNumberElement = document.querySelector(
      ".woocommerce-order-overview__order strong"
    );

    if (emailElement) {
      console.log("✅ Email:", emailElement.textContent);
    } else {
      console.log("🚫 Email:", emailElement);
      isValid = false;
    }

    if (totalAmountElement) {
      console.log("✅ Total Amount:", totalAmountElement.textContent);
    } else {
      console.log("🚫 Total Amount:", totalAmountElement);
      isValid = false;
    }

    if (orderNumberElement) {
      console.log("✅ Order number:", orderNumberElement.textContent);
    } else {
      console.log("🚫 Order number:", orderNumberElement);
      isValid = false;
    }

    if (isValid) {
      const totalAmount = parseFloat(
        totalAmountElement ? tParseAmount(totalAmountElement.textContent) : 0
      );

      tCheckTransmissionSale({
        email: emailElement.textContent.trim(),
        total_amount: totalAmount,
      })
        .then(async (res) => {
          if (res.status === 200) {
            const json = await res.json();
            console.log("🕒 Checked sale, is already pending:", json.id);

            tUpdateTransmissionSale(json.id, {
              transmission_id: json.transmission_id,
              order_number: orderNumberElement.textContent.trim(),
              status_url: window.location.href,
              status: true,
            }).then((res) => {
              if (res.status === 200) {
                console.log("✅ Sale updated successfully!");
              } else {
                console.log("🚫 Error updating the sale");
              }
            });
          } else {
            console.log("✅ This sale is already completed");
          }
        })
        .catch((error) => {
          console.log("🚫 Error checking the sale:", error.message);
        });
    }
  }

  if (isCheckoutPage && isFromTipztv) {
    const id = params.get("id");

    console.log("✅ Tipztv");
    console.log("✅ Checkout Page");
    console.log("✅ Transmission ID:", id);

    const form = document.querySelector("form.woocommerce-checkout");
    const totalAmountElement = document.querySelector(
      ".order-total .woocommerce-Price-amount bdi"
    );

    let totalAmount = 0;

    if (form) {
      console.log("✅ Form checkout:", form);
    } else {
      console.log("🚫 Form checkout:", form);
      isValid = false;
    }

    if (totalAmountElement) {
      console.log("✅ Total Amount:", totalAmountElement.textContent);
    } else {
      console.log("🚫 Total Amount:", totalAmountElement);
      isValid = false;
    }

    try {
      totalAmount = parseFloat(
        totalAmountElement ? tParseAmount(totalAmountElement.textContent) : 0
      );

      if (Number.isNaN(totalAmount)) {
        console.log("🚫 Total Amount Format:", totalAmount);
        isValid = false;
      }

      if (!Number.isNaN(totalAmount) && totalAmount > 0) {
        console.log("✅ Total Amount Format:", totalAmount);
      }
    } catch (error) {
      console.log("🚫 Total Amount Format:", totalAmountElement.textContent);
      isValid = false;
    }

    form.addEventListener("submit", (e) => {
      e.preventDefault();

      const emailInput = document.querySelector("input[type=email]");
      const firstNameInput = document.querySelector(
        "input[name=billing_first_name]"
      );
      const lastNameInput = document.querySelector(
        "input[name=billing_last_name]"
      );
      const email = emailInput.value;
      const firstName = firstNameInput.value;
      const lastName = lastNameInput.value;
      const fullName = `${firstName} ${lastName}`;
      let totalQuantity = 0;

      const $quantities = document.querySelectorAll("input[id^=quantity]");

      if ($quantities && $quantities.length > 0) {
        $quantities.forEach((item) => {
          totalQuantity += parseInt(item?.value || "0");
        });
      }

      if (email && email !== "") {
        console.log("✅ Email:", email);
      } else {
        console.log("🚫 Email: empty");
        isValid = false;
      }

      if (firstName && firstName !== "") {
        console.log("✅ FullName:", firstName);
      } else {
        console.log("🚫 FullName: empty");
        isValid = false;
      }

      if (isValid) {
        tCreateTransmissionSale({
          checkout_id: 0,
          quantity: totalQuantity,
          transmission_id: parseInt(id),
          total_amount: totalAmount,
          full_name: fullName,
          email: email,
          order_number: "",
          status: false,
          status_url: "",
        });
      }
    });
  }
};

const tCheckMicroSiteEmbed = () => {
  const $iframe = jQuery("iframe.t-expanded");
  const $header = jQuery("header");

  if ($iframe && $iframe.length > 0 && $header && $header.length > 0) {
    const headerHeight = $header[0].offsetHeight;

    $iframe.css("top", `${headerHeight}px`);
    $iframe.css("height", `calc(100vh - ${headerHeight}px)`);
  }
};

const tActiveHomeWidget = () => {};

const tCreateTransmissionSale = (data) => {
  return fetch("https://go.tipzty.com/transmissionsales", {
    method: "POST",
    body: JSON.stringify(data),
    headers: {
      "Content-Type": "application/json",
    },
  });
};

const tUpdateTransmissionSale = (id, data) => {
  return fetch("https://go.tipzty.com/transmissionsales/" + id, {
    method: "PUT",
    body: JSON.stringify(data),
    headers: {
      "Content-Type": "application/json",
    },
  });
};

const tCheckTransmissionSale = (data) => {
  return fetch("https://api.tipzty.com/api/check-transmission-sale", {
    method: "POST",
    body: JSON.stringify(data),
    headers: {
      "Content-Type": "application/json",
    },
  });
};

const tParseAmount = (amount) => {
  return parseInt(
    amount.replace("$", "").replace(/\,/g, "").replace(/\./g, "").trim()
  );
};

function tToggleWidget() {
  const isHome = !document.body.classList.contains("single-product");
  const parentClass = isHome ? ".t-home-widget" : ".t-widget";
  const review = document.querySelector(`${parentClass} .t-review`);
  const iframe = document.querySelector(`${parentClass} .t-iframe`);
  const preview = document.querySelector(`${parentClass} .t-block-preview`);
  const btn = document.querySelector(`${parentClass} .t-logo`);
  const videoUrl = localStorage.getItem("@iframe_tipzty_url");

  if (review.style.visibility == "visible") {
    review.style.visibility = "hidden";
    iframe.src = "";

    if (btn) {
      if (isHome) {
        btn.src = btn.src.replace("close.png", "live.png");
      } else {
        btn.src = btn.src.replace("close.png", "btn.png");
      }

      btn.classList.add("animate");
    }

    review.classList.remove("t-size-md");

    if (preview) {
      preview.classList.remove("t-block-preview-close");
      preview.innerHTML = `
        <div class="t-block-preview-close-icon" onclick="tToggleButtonPlay()">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="#2A3D44" width="20" height="20"><path d="M0 256C0 114.6 114.6 0 256 0C397.4 0 512 114.6 512 256C512 397.4 397.4 512 256 512C114.6 512 0 397.4 0 256zM175 208.1L222.1 255.1L175 303C165.7 312.4 165.7 327.6 175 336.1C184.4 346.3 199.6 346.3 208.1 336.1L255.1 289.9L303 336.1C312.4 346.3 327.6 346.3 336.1 336.1C346.3 327.6 346.3 312.4 336.1 303L289.9 255.1L336.1 208.1C346.3 199.6 346.3 184.4 336.1 175C327.6 165.7 312.4 165.7 303 175L255.1 222.1L208.1 175C199.6 165.7 184.4 165.7 175 175C165.7 184.4 165.7 199.6 175 208.1V208.1z"/></svg>
        </div>
        <video playsinline class="t-video" src="${videoUrl}" width="100" height="100" onclick="tToggleWidget()" loop autoplay muted preload></video>
        <p id="video-text" onclick="tToggleWidget()">${videoTextConstant}</p>
      `;
    }
  } else {
    const src = localStorage.getItem("@iframe_tipzty");

    if (iframe.src !== src) {
      iframe.src = src;
    }

    review.style.visibility = "visible";

    setTimeout(() => {
      review.classList.add("t-review-shadow");
    }, 2000);

    if (btn) {
      if (isHome) {
        btn.src = btn.src.replace("live.png", "close.png");
      } else {
        btn.src = btn.src.replace("btn.png", "close.png");
      }

      btn.classList.remove("animate");
    }

    if (preview) {
      preview.classList.add("t-block-preview-close");
      preview.innerHTML = `
        <img class="t-logo" src="${T_CDN_URL}/images/close.png" width="60" height="60" onclick="tToggleWidget()" loading="lazy" />
      `;
    }
  }
}

async function tNewProduct(token, prod, urlImage) {
  var urlProduct = window.location.href;
  var cant = 0;

  if (prod.stock_quantity != null) {
    cant = prod.stock_quantity;
  }

  var body = {
    shop_id: prod.id,
    name: prod.name,
    description: prod.description,
    short_description: prod.short_description,
    price: parseInt(prod.price),
    discount: parseInt(prod.sale_price),
    stock: cant,
    avatar: urlImage,
    url_storage: urlProduct,
  };

  await fetch("https://go.tipzty.com/api/products", {
    method: "POST",
    body: JSON.stringify(body),
    headers: {
      "X-Tipzty-Key": token,
      "content-type": "application/json",
    },
  }).then((resp) => {
    console.log("product created:", resp.json());
  });
}

const tParseBrandName = (brandName) => {
  const brandNameNormalized = brandName
    .replace(/\d/g, "")
    .replace("á", "a")
    .replace("é", "e")
    .replace("í", "i")
    .replace("ó", "o")
    .replace("ú", "u");

  return brandNameNormalized.replace(/ /g, "").toLowerCase().trim();
};

const tSetVideoSource = (videoUrl) => {
  const isHome = !document.body.classList.contains("single-product");
  const parentClass = isHome ? ".t-home-widget" : ".t-widget";
  const $block = document.querySelector(`${parentClass} .t-block-preview`);
  const $tVideo = document.querySelector(`${parentClass} .t-video`);

  $block.style.visibility = "visible";

  if ($tVideo) {
    $tVideo.src = videoUrl;
    try {
      localStorage.setItem("@iframe_tipzty_url", videoUrl);
    } catch (e) {}
    setTimeout(() => {
      $tVideo.play();
    }, 2000);
  }
};

function tToggleButtonPlay() {
  const isHome = !document.body.classList.contains("single-product");
  const parentClass = isHome ? ".t-home-widget" : ".t-widget";
  const blockPreview = document.querySelector(
    `${parentClass} .t-block-preview`
  );
  const blockPlay = document.querySelector(`${parentClass} .t-block-play`);

  if (blockPreview.style.visibility === "visible") {
    blockPreview.style.visibility = "hidden";
    blockPlay.style.visibility = "visible";
  } else {
    blockPreview.style.visibility = "visible";
    blockPlay.style.visibility = "hidden";
  }
}

function getBrandConfiguration() {
  const domain = window.location.origin
    .replace("https://", "")
    .replace(/www\./g, "");
  const configuration = tConfigurations[domain] || { position: "right" };

  return configuration;
}

function getClassWidgetPosition(configuration) {
  return configuration.position === "left" ? "t-left-16" : "t-right-16";
}

function getClassOverlayPosition(configuration) {
  return configuration.position === "left"
    ? "t-overlay-left"
    : "t-overlay-right";
}

function tChangeVideoText(text) {
  const videoText = document.getElementById("video-text");
  const newText = `${text} 👀`;

  videoTextConstant = newText;
  videoText.textContent = newText;
}

function tCheckIsPageProducts() {
  const url = window.location.href.replace(window.location.search, "");

  return (
    url.includes("products") ||
    url.includes("productos") ||
    url.includes("product")
  );
}

function tCheckReviewUrls(reviewUrls) {
  if (
    reviewUrls === null ||
    reviewUrls === undefined ||
    reviewUrls === "" ||
    reviewUrls === "*"
  )
    return true;

  const url = window.location.pathname;
  const urls = reviewUrls.split(",");

  return urls.indexOf(url) !== -1;
}
