document.addEventListener('DOMContentLoaded', function () {
    // Utility function to initialize intlTelInput and validation
    function initializePhoneField(phoneInputField) {
        // console.log(phoneInputField); // Ensure the correct element is selected
        
        const iti = window.intlTelInput(phoneInputField, {
            initialCountry: "rs",
            preferredCountries: ["rs", "hr"],
            allowDropdown: true,
            countrySearch: true,
            separateDialCode: true,
            formatOnDisplay: true,
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
        });

        // Log country changes
        phoneInputField.addEventListener('countrychange', function () {
            console.log('Country changed to: ', iti.getSelectedCountryData().iso2);
        });

        // Add validation message container
        const msgContainer = phoneInputField.closest('.cmb-td');
        if (msgContainer) {
            const validationMessageContainer = document.createElement('div');
            validationMessageContainer.classList.add('validation-message');
            validationMessageContainer.textContent = ''; // Initially empty
            msgContainer.appendChild(validationMessageContainer);

            // Add validation on blur
            phoneInputField.addEventListener('blur', function () {
                validationMessageContainer.textContent = iti.isValidNumber()
                    ? ''
                    : 'Molimo unesite važeći broj telefona.';
            });

            // Clear validation message while typing
            phoneInputField.addEventListener('input', function () {
                validationMessageContainer.textContent = '';
            });
        }
		
		// Attach form submission logic
        const form = phoneInputField.closest('form');
        if (form) {
            form.addEventListener('submit', function (event) {
                // Prevent submission if phone number is invalid
                if (!iti.isValidNumber()) {
                    event.preventDefault();
                    alert('Molimo unesite važeći broj telefona.');
                    return;
                }
                // Set the full phone number (dial code + number) into the input field
                phoneInputField.value = iti.getNumber();
            });
        }
    }

	const phoneInputField = document.getElementsByClassName('phone-with-flags')[1];
	if (phoneInputField) {
		initializePhoneField(phoneInputField);
	}
	
	// Register Employer Page
	// Get the second `.cmb2-id--employer-phone` container
	const secondContainer = document.querySelectorAll('.cmb2-id--employer-phone')[1];

	// Find the input field within the second container
	if (secondContainer) {
		const phoneInputFieldEmpReg = secondContainer.querySelector('#_employer_phone');
		if (phoneInputFieldEmpReg) {
			initializePhoneField(phoneInputFieldEmpReg);
		} else {
			console.log('Input field with ID _employer_phone not found in the second container.');
		}
	} 
	
    // Check if the body has the specific class for the target page
    if (document.body.classList.contains('page-template-page-dashboard')) {
		console.log('uso je');
        // Dashboard Candidate page
        let phoneInputField = document.querySelector('.phone-with-flags');
        if (phoneInputField) {
            initializePhoneField(phoneInputField);
        }
		
		// Dashboard Employer Page
		const phoneInputEmployer = document.getElementById('_employer_phone');
		if (phoneInputEmployer) {
			initializePhoneField(phoneInputEmployer);
		}
		
		// Dashboard Employer page - Representative phone
		let phoneInputRepresentative = document.getElementById('custom-text-3318838');
		if (phoneInputRepresentative) {
            initializePhoneField(phoneInputRepresentative);
        }
    }
	
});



