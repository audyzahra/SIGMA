function scrollToAspirasi() {

    history.replaceState(null, null, ' ');

    const aspirasi = document.getElementById('aspirasi');

    if (aspirasi) {
        aspirasi.scrollIntoView({
            behavior: 'smooth'
        });
    }
}