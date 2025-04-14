document.querySelector('.contact_wrapper form').addEventListener('submit', function(event) {
    event.preventDefault();

    var name = document.querySelector('input[name="name"]').value;
    var email = document.querySelector('input[name="email"]').value;
    var tel = document.querySelector('input[name="tel"]').value;
    var subject = document.querySelector('input[name="subject"]').value;
    var message = document.querySelector('textarea[name="message"]').value;

    if (name && email.includes('@') && tel && subject && message) {
        document.getElementById('contact-form').style.display = 'none';
        document.getElementById('message-sent').style.display = 'block';
        document.getElementById('message-error').style.display = 'none';
    } else {
        document.getElementById('message-error').style.display = 'block';
    }
});

document.querySelector('input[name="tel"]').addEventListener('input', function(e) {
    e.target.value = e.target.value
        .replace(/\D/g, '')
        .replace(/^(\d{2})(\d)/g, '($1) $2')
        .replace(/(\d)(\d{4})$/, '$1-$2');
});
