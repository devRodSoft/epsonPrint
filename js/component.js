var app = new Vue({
    el: '#app',
    data: {
      name: '',
      celular: '',
      anticipo: 0,
      
      products: [{
            qty: 1,
            desc: '',
            price: 0  
        }],
      total: 0,
      anticipo: 0,
      restante: 0
    },
    methods:  {
        
        addItem: function addItem() {
            this.products.push({
                qty: 1,
                desc: '',
                price: 0  
            });
        },
        
        removeItem: function removeItem(index) {
            this.products.splice(index, 1);
            this.getTotal()
        },

        printTicket: function printTicket() {
                let data  = {
                
                    name: this.name,
                    celphone: this.celular,
                    total: this.total,
                    anticipo: this.anticipo,
                    restante: this.restante,
                    products: this.products,
    
                }
                /*var xhr = new XMLHttpRequest();
                xhr.open("POST", "http://localhost/reinkrea/nota.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
                xhr.send(JSON.stringify(data));*/

                const requestOptions = {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(data)
                  };
                  fetch("http://localhost/reinkrea/nota.php", requestOptions)
                    .then(response => response.json());
        },
        handleBlur: function handleBlur() {
            this.getTotal();
        },
        getTotal: function getTotal() {
           this.total = parseInt(this.products.reduce((a, {price}) => parseFloat(a) + parseFloat(price), 0));
           this.restante = this.total - this.anticipo;
        },
    }
  })