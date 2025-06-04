
console.log("LOADED: keydown-shortcuts.js");


if (window.location.href.includes('wp-admin/theme-editor.php')) {

	document.addEventListener('keydown', function (e) {
	  if ((e.ctrlKey || e.metaKey) && e.key === 's') {
	    e.preventDefault();
	    const submitButton = document.getElementById('submit');
	    if (submitButton) {
	      submitButton.click();
	    }
	  }
	});

}

if (window.location.href.includes('wp-admin/customize.php')) {
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            const submitButton = document.getElementById('save');
            if (submitButton) {
                submitButton.click();
                wp.customize.previewer.refresh();
            }
        }
    });
}




if (window.location.href.includes('post.php') && window.location.href.includes('action=edit')) {

	document.addEventListener('keydown', function (e) {
		if ((e.ctrlKey || e.metaKey) && e.key === 's') {
			e.preventDefault();
			const publishButtonContainer = document.getElementById('publishing-action');
			if (publishButtonContainer) {
				const button = publishButtonContainer.querySelector('input[type="submit"], button[type="submit"]');
				if (button) {
					button.click();
				}
			}
		}
	});

}

