function validateForm() {
  const fname = document.getElementsByName("fname")[0].value.trim();
  const lname = document.getElementsByName("lname")[0].value.trim();
  const email = document.getElementsByName("email_id")[0].value.trim();
  const password = document.getElementsByName("password")[0].value.trim();
  const contact = document.getElementsByName("contact_no")[0].value.trim();
  const gender = document.getElementsByName("gender");
  const houseno = document.getElementsByName("houseno")[0].value.trim();
  const street = document.getElementsByName("street")[0].value.trim();
  const city = document.getElementsByName("city")[0].value.trim();
  const state = document.getElementsByName("state")[0].value.trim();
  const pincode = document.getElementsByName("pincode")[0].value.trim();

  if (fname === "" || lname === "") {
    alert("Please enter your first and last name.");
    return false;
  }

  const emailpattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;
  if (!emailpattern.test(email)) {
    alert("Please enter a valid email address.");
    return false;
  }

  if (password.length < 6) {
    alert("Password must be at least 6 characters long.");
    return false;
  }

  const phonepattern = /^[0-9]{10}$/;
  if (!phonepattern.test(contact)) {
    alert("Contact number must be 10 digits.");
    return false;
  }

  let genderSelected = false;
  for (let i = 0; i < gender.length; i++) {
    if (gender[i].checked) {
      genderSelected = true;
      break;
    }
  }
  if (!genderSelected) {s
    alert("Please select your gender.");
    return false;
  }

  if (houseno === "" || street === "" || city === "" || state === "" || pincode === "") {
    alert("Please fill in all address fields.");
    return false;
  }

  const pinPattern = /^[0-9]{6}$/;
  if (!pinPattern.test(pincode)) {
    alert("Please enter a valid 6-digit pincode.");
    return false;
  }
 
  return true;
}
