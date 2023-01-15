"use strict";
class Popup
{
  constructor()
  {
    this.init();
    this.handlers();
  }
  
  init()
  {
    this.buttons = document.getElementsByClassName('js-popup-trigger');
    this.submit = document.getElementById('popup-submit');
    this.form = document.getElementById('popup-form');
    this.popup = document.getElementById('popup');
    this.popup.classList.add('popup-loaded');
    this.setMask('[type="tel"]', '+7 (___) ___-__-__');
  }

  handlers()
  {
    for (let i = 0; i < this.buttons.length; i++) {
      this.buttons[i].addEventListener('click', this.togglePopup.bind(this), { passive: true });
    }
    this.submit.addEventListener('click', this.sendData.bind(this), { passive: true });
  }

  togglePopup()
  {
    this.popup.classList.toggle('active');
  }


  validate(data) {
    this.errors = [];

    for (let key in data) {
      if (key === 'email') this.errors['email'] = !/^[^@]+@.*.[a-z]{2,15}$/i.test(data[key]);
      if (key === 'phone') this.errors['phone'] = !/^\+7\s\(\d{3}\)\s\d{3}\-\d{2}-\d{2}$/.test(data[key]);
    }

    return Object.values(this.errors).reduce((prev, curr)=>{ return curr === true ? prev + 1 : prev }, 0) > 0 ? 0 : 1;
  }

  getData() {
    let data = {};
    Object.keys(this.form.elements).forEach(key => {
        let element = this.form.elements[key];
        if (element.type !== "submit" && element.type !== "button" ) {
          data[element.name] = element.value;
        }
    });
    return data;
  }

  sendData() {
    
    let data = this.getData();

    if (this.validate(data)) {

      this.handleErrors();

      this.submit.setAttribute('disabled', 'disabled');
      this.submit.querySelector('.button3d__front').classList.add('spinner');

      let formData = new FormData();
      for(let key in data) {
        formData.set(key, data[key]);
      }

      let promise = fetch('/system/core.php', {
        method: 'POST',
        headers: {
          'Accept': 'application/json'
        },
        body: formData
      });

      promise
        .then((res) => res.json())
        .then((data) => {
          this.submit.querySelector('.button3d__front').classList.remove('spinner');
          switch (data['code']) {
            case 'success':
              this.togglePopup();
              console.info(data['message']);
              break;

            case 'core-error':
              this.togglePopup();
              console.error(data['message']);
              break;

            case 'error-validate':
              this.errors = data['errors'];
              this.handleErrors();
              this.submit.removeAttribute('disabled');
              console.error(data['message']);
              break;

            default:
              break;
          }
        })
        .catch((error) => { 
          this.submit.querySelector('.button3d__front').classList.remove('spinner');
          this.togglePopup();
          console.error(error)
        })

    } else {
      this.handleErrors();
    }
  }

  handleErrors() {
    for (let key in this.errors) {
      const input = this.form.querySelector(`[name="${key}"]`).parentNode;
      if (this.errors[key]) {
        input.classList.add('has-error');
      } else {
        input.classList.remove('has-error');
      }
    }
  }

  setMask(selector, masked) {
    const nodes = document.querySelectorAll(selector);

    var keyCode;

    function maskPhone(event) {
        this.selectionStart = this.selectionEnd = this.value.length;
        event.keyCode && (keyCode = event.keyCode);
        var pos = this.selectionStart;
        
        if (pos < 3) event.preventDefault();
        var matrix = masked,
            i = 0,
            def = matrix.replace(/\D/g, ""),
            val = this.value.replace(/\D/g, ""),
            new_value = matrix.replace(/[_\d]/g, function(a) {
                return i < val.length ? val.charAt(i++) || def.charAt(i) : a
            });
        i = new_value.indexOf("_");
        if (i != -1) {
            i < 5 && (i = 3);
            new_value = new_value.slice(0, i)
        }
        var reg = matrix.substr(0, this.value.length).replace(/_+/g,
            function(a) {
                return "\\d{1," + a.length + "}"
            }).replace(/[+()]/g, "\\$&");
        reg = new RegExp("^" + reg + "$");
        if (!reg.test(this.value) || this.value.length < 5 || keyCode > 47 && keyCode < 58) this.value = new_value;
        if (event.type == "blur" && this.value.length < 5)  this.value = ""
    }

    for (const node of nodes) {
      node.addEventListener("input", maskPhone, false);
      node.addEventListener("focus", maskPhone, false);
      node.addEventListener("blur", maskPhone, false);
      node.addEventListener("keydown", maskPhone, false);
    }
  }
  
}

const popup = new Popup();