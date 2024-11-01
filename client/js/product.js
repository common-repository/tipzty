jQuery(document).ready(() => {
  console.log("🔌 Product Js Loaded v1.9.2");

  const isProductPage = !!document.body.classList.contains("single-product");

  if (isProductPage) {
    tActivateExtension();
  }
});

async function tActivateExtension() {
  console.log("✅ Product Widget Activated");

  var productItem = localStorage.getItem("@product");

  if (productItem !== undefined || productItem !== "undefined") {
    console.log("✅ Product exists in localStorage");
  } else {
    console.log("🚫 Token not registered");
    return;
  }

  var product = JSON.parse(productItem);
  var token = localStorage.getItem("@token_tipzty_plugin");
  let liveExists = false;

  if (token !== undefined && token !== "") {
    console.log("✅ Token registered");
  } else {
    console.log("🚫 Token not registered");
    return;
  }

  const productId = product.id;
  const productUrl = window.location.href;
  // const productUrl = product.url;

  var responseApi = await fetch(
    `https://go.tipzty.com/api/products/reviews/${productId}?url=${productUrl}`,
    {
      method: "GET",
      headers: {
        "X-Tipzty-Key": token,
      },
    }
  ).then((resp) => resp.json());

  var responseTransmissionsApi = await fetch(
    `https://go.tipzty.com/api/products/transmissions/${productId}?url=${productUrl}`,
    {
      method: "GET",
      headers: {
        "X-Tipzty-Key": token,
      },
    }
  ).then((resp) => resp.json());

  if (responseTransmissionsApi.count !== 0 && !tCheckIsPageProducts()) {
    const transmission = responseTransmissionsApi.transmissions.find(
      (t) => t.status_text === "pending" || t.status_text === "recording"
    );

    if (transmission) {
      const brandName = tParseBrandName(transmission.brand.full_name);
      const url = `https://tipz.tv/${brandName}/${transmission.id}?mode=widget`;

      liveExists = true;

      document.querySelector(".t-widget").style.display = "block";

      if (block) {
        block.style.visibility = "visible";
      }

      localStorage.setItem("@iframe_tipzty", url);
      tChangeVideoText("Live");
      tSetVideoSource(transmission.preview_video);
    }
  }

  if (responseApi.count !== 0 && !liveExists) {
    const reviews = responseApi.reviews.slice().sort((x, y) => y.id - x.id);
    const review = reviews[0];
    const brandName = tParseBrandName(review.brand.full_name);
    const url = `https://tipz.tv/${
      responseApi.count > 1 ? "r" : "review"
    }/${brandName}?id=${review.id}&mode=widget`;

    if (tCheckReviewUrls(review.widget_url)) {
      if (review.mode === "horizontal") {
        const $review = document.querySelector(".t-widget .t-review");

        $review.classList.add("t-horizontal");
      }

      localStorage.setItem("@iframe_tipzty", url);
      tChangeVideoText("Video");
      document.querySelector(".t-widget").style.display = "block";

      const block = document.querySelector(".t-widget .t-block");

      if (block) {
        block.style.visibility = "visible";
      }

      tSetVideoSource(review.video_short_url);
    }
  }
}
