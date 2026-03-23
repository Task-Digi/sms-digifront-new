
function showReturnMessages(content, type = 'error', time = 5000, appendClass = 'js-messages') {
    let classes = 'alert-danger';

    if(type === 'success') {
        classes = 'alert-success';
    }
    let message = '<div class="alert '+classes+' alert-dismissible fade show mt-3" role="alert">'+content;
    message +='<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
    $('.'+appendClass).empty().append(message);
    setTimeout(function(){ $('.'+appendClass).empty(); }, time);
}

/* Add to Home Screen Code Below */
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('service-worker.js').then(function(registration) {
            // Registration was successful
            console.log('ServiceWorker registration successful with scope: ', registration.scope);
        }, function(err) {
            // registration failed :(
            console.log('ServiceWorker registration failed: ', err);
        });
    });
}

// Initialize deferredPrompt for use later to show browser install prompt.
/*let deferredPrompt;
window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent the mini-infobar from appearing on mobile
    e.preventDefault();
    // Stash the event so it can be triggered later.
    deferredPrompt = e;
    // Update UI notify the user they can install the PWA
    showInstallPromotion();
    // Optionally, send analytics event that PWA install promo was shown.
    console.log(`'beforeinstallprompt' event was fired.`);
});*/

/*const addBtn = document.querySelector('.add-button');
addBtn.addEventListener('click', (e) => {
    // Hide the app provided install promotion
    // hideInstallPromotion();
    // Show the install prompt
    if (deferredPrompt) {

        deferredPrompt.prompt();
        // Wait for the user to respond to the prompt
        const { outcome } = deferredPrompt.userChoice;
        // Optionally, send analytics event with outcome of user choice
        console.log(`User response to the install prompt: ${outcome}`);

        // ...
    }
    // deferredPrompt.prompt();
    // We've used the prompt, and can't use it again, throw it away
    deferredPrompt = null;
});*/

/*window.addEventListener('appinstalled', () => {
    // Hide the app-provided install promotion
    // hideInstallPromotion();
    // Clear the deferredPrompt so it can be garbage collected
    deferredPrompt = null;
    // Optionally, send analytics event to indicate successful install
    console.log('PWA was installed');
});*/

$(document).ready(function () {

    const divInstall = document.getElementById('installContainer');
    const buttonInstall = document.getElementById('buttonInstall');

    window.addEventListener('beforeinstallprompt', (event) => {
        console.log('👍', 'beforeinstallprompt', event);
        // Stash the event so it can be triggered later.
        window.deferredPrompt = event;
        // Remove the 'd-none' class from the install button container
        divInstall.classList.toggle('d-none', false);
    });

    buttonInstall.addEventListener('click', async () => {
        console.log('👍', 'butInstall-clicked');
        const promptEvent = window.deferredPrompt;
        if (!promptEvent) {
            // The deferred prompt isn't available.
            return;
        }
        // Show the install prompt.
        promptEvent.prompt();
        // Log the result
        const result = await promptEvent.userChoice;
        console.log('👍', 'userChoice', result);
        // Reset the deferred prompt variable, since
        // prompt() can only be called once.
        window.deferredPrompt = null;
        // Hide the install button.
        divInstall.classList.toggle('d-none', true);
    });

    window.addEventListener('appinstalled', (event) => {
        console.log('👍', 'appinstalled', event);
        // Clear the deferredPrompt so it can be garbage collected
        window.deferredPrompt = null;
    });
});
