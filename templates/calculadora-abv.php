
  <style>

    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }
    input {
      width: 100%;
      padding: 8px;
      font-size: 16px;
      margin-top: 5px;
      box-sizing: border-box;
    }
    button {
      margin-top: 20px;
      padding: 10px;
      width: 100%;
      background-color: #007bff;
      border: none;
      color: white;
      font-size: 16px;
      border-radius: 5px;
      cursor: pointer;
    }
    button:hover {
      background-color: #0056b3;
    }
    #resultado {
      margin-top: 20px;
      font-size: 18px;
      text-align: center;
      font-weight: bold;
    }
  </style>

  <h1>Calculadora %ABV</h1>

  <label for="og">OG (Gravedad Original):</label>
  <input type="number" id="og" step="0.001" min="1.000" value="1.050">

  <label for="fg">FG (Gravedad Final):</label>
  <input type="number" id="fg" step="0.001" min="0.980" value="1.010">

  <button onclick="calcularABV()">Calcular ABV</button>

  <div id="resultado"></div>

  <script>
    function calcularABV() {
      const og = parseFloat(document.getElementById("og").value);
      const fg = parseFloat(document.getElementById("fg").value);

      if (isNaN(og) || isNaN(fg) || og <= fg) {
        document.getElementById("resultado").innerText = "Por favor, introduce valores válidos (OG debe ser mayor que FG).";
        return;
      }

      const abv = (og - fg) * 105 * 1.25;
      document.getElementById("resultado").innerText = `Alcohol estimado: ${abv.toFixed(2)}% ABV`;
    }
  </script>
