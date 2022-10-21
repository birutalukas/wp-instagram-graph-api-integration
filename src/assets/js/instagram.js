let instaPosts = document.querySelectorAll(".insta__item");

for (let i = 0; i < instaPosts.length; i++) {
    let instaPostsUrl = instaPosts[i].getAttribute("data-image-url");

    let options = {
        root: null,
        rootMargin: "50px",
        threshold: 0,
    };
    let callback = (entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                instaPosts[i].style.backgroundImage =
                    "url('" + instaPostsUrl + "')";
            }
        });
    };

    let observer = new IntersectionObserver(callback, options);

    observer.observe(instaPosts[i]);
}
