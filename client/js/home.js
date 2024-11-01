jQuery(document).ready(() => {
  console.log("🔌 Home Js Loaded v1.9.2");

  const isProductPage = !!document.body.classList.contains("single-product");

  if (!isProductPage) {
    tActivateExtensionHome();
  }
});

async function tActivateExtensionHome() {
  console.log("✅ Home Widget Activated");

  var token = localStorage.getItem("@token_tipzty_plugin");
  let errored = false;
  let liveExists = false;

  if (token !== undefined && token !== "") {
    console.log("✅ Token registered");
  } else {
    console.log("🚫 Token not registered");
    return;
  }

  var responseReviewsApi = await fetch(
    "https://go.tipzty.com/api/brand/reviews",
    {
      method: "GET",
      headers: {
        "X-Tipzty-Key": token,
      },
    }
  ).then((resp) => {
    if (resp.ok) {
      return resp.json();
    } else {
      errored = true;
    }
  });

  var responseTransmissionsApi = await fetch(
    "https://go.tipzty.com/api/brand/transmissions",
    {
      method: "GET",
      headers: {
        "X-Tipzty-Key": token,
      },
    }
  ).then((resp) => {
    if (resp.ok) {
      return resp.json();
    } else {
      errored = true;
    }
  });

  if (
    !tCheckIsPageProducts() &&
    responseTransmissionsApi &&
    (responseTransmissionsApi.status_text === "pending" ||
      responseTransmissionsApi.status_text === "recording")
  ) {
    const id = responseTransmissionsApi.id;
    const brand = tParseBrandName(responseTransmissionsApi.brand.full_name);
    const url = `https://tipz.tv/${brand}/${id}?mode=widget`;

    document.querySelector(".t-home-widget").style.display = "block";

    liveExists = true;

    try {
      localStorage.setItem("@iframe_tipzty", url);
    } catch (e) {}

    tChangeVideoText("Live");
    tSetVideoSource(responseTransmissionsApi.preview_video);
  }

  if (responseReviewsApi && responseReviewsApi.count > 0 && !liveExists) {
    const review = responseReviewsApi.reviews[0];
    const brandName = tParseBrandName(review.brand.full_name);
    const url = `https://tipz.tv/review/${brandName}?id=${review.id}&mode=widget`;

    if (tCheckReviewUrls(review.widget_url)) {
      document.querySelector(".t-home-widget").style.display = "block";

      if (review.mode === "horizontal") {
        const $review = document.querySelector(".t-home-widget .t-review");

        $review.classList.add("t-horizontal");
      }

      try {
        localStorage.setItem("@iframe_tipzty", url);
      } catch (e) {}

      tChangeVideoText("Video");
      tSetVideoSource(review.video_short_url);
    }
  }
}
