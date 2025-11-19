<title>Calculadora de Maceración (Sistema Métrico)</title>
</head>
<body>
  <h1>Calculadora de Maceración (Infusión - Sistema Métrico)</h1>

  <h2>Agua de Ataque</h2>
  <label for="grano">Peso del grano (kg):</label>
  <input type="number" id="grano"><br><br>

  <label for="tempGrano">Temperatura del grano (°C):</label>
  <input type="number" id="tempGrano"><br><br>

  <label for="tempObjetivo">Temperatura objetivo de maceración (°C):</label>
  <input type="number" id="tempObjetivo"><br><br>

  <label for="ratio">Relación agua/grano (L/kg):</label>
  <input type="number" step="0.01" id="ratio"><br><br>

  <button onclick="calcularAguaAtaque()">Calcular Temperatura del Agua de Ataque</button>
  <p id="resultadoAtaque"></p>

  <h2>Infusión Adicional</h2>
  <label for="tempActual">Temperatura actual del macerado (°C):</label>
  <input type="number" id="tempActual"><br><br>

  <label for="nuevaTemp">Nueva temperatura objetivo (°C):</label>
  <input type="number" id="nuevaTemp"><br><br>

  <label for="aguaActual">Volumen actual de agua en el macerado (L):</label>
  <input type="number" id="aguaActual"><br><br>

  <label for="tempInfusion">Temperatura del agua de infusión (°C):</label>
  <input type="number" id="tempInfusion" value="100"><br><br>

  <button onclick="calcularInfusion()">Calcular Volumen de Agua para Infusión</button>
  <p id="resultadoInfusion"></p>

  <h2>Volumen Total del Macerado</h2>
  <button onclick="calcularVolumenTotal()">Calcular Volumen Total del Macerado</button>
  <p id="resultadoVolumenTotal"></p>

  <script>
    function calcularTemperaturaAguaAtaque(tempObjetivo, tempGrano, relacionAguaGrano) {
      var Tw = (0.41 / relacionAguaGrano) * (tempObjetivo - tempGrano) + tempObjetivo;
      return Tw;
    }

    function calcularVolumenInfusion(pesoGrano, tempActual, nuevaTemp, aguaMacerado, tempAguaInfusion) {
      var numerador = (nuevaTemp - tempActual) * (0.41 * pesoGrano + aguaMacerado);
      var denominador = tempAguaInfusion - nuevaTemp;
      return numerador / denominador;
    }

    function calcularAguaAtaque() {
      var grano = parseFloat(document.getElementById("grano").value);
      var tempGrano = parseFloat(document.getElementById("tempGrano").value);
      var tempObjetivo = parseFloat(document.getElementById("tempObjetivo").value);
      var ratio = parseFloat(document.getElementById("ratio").value);

      if (isNaN(grano) || isNaN(tempGrano) || isNaN(tempObjetivo) || isNaN(ratio)) {
        document.getElementById("resultadoAtaque").innerText = "Por favor completa todos los campos correctamente.";
        return;
      }

      var resultado = calcularTemperaturaAguaAtaque(tempObjetivo, tempGrano, ratio);
      document.getElementById("resultadoAtaque").innerText = "Temperatura del agua de ataque: " + resultado.toFixed(2) + " °C";
    }

    function calcularInfusion() {
      var grano = parseFloat(document.getElementById("grano").value);
      var tempActual = parseFloat(document.getElementById("tempActual").value);
      var nuevaTemp = parseFloat(document.getElementById("nuevaTemp").value);
      var aguaActual = parseFloat(document.getElementById("aguaActual").value);
      var tempInfusion = parseFloat(document.getElementById("tempInfusion").value);

      if (isNaN(grano) || isNaN(tempActual) || isNaN(nuevaTemp) || isNaN(aguaActual) || isNaN(tempInfusion)) {
        document.getElementById("resultadoInfusion").innerText = "Por favor completa todos los campos correctamente.";
        return;
      }

      var resultado = calcularVolumenInfusion(grano, tempActual, nuevaTemp, aguaActual, tempInfusion);
      document.getElementById("resultadoInfusion").innerText = "Volumen de agua para infusión: " + resultado.toFixed(2) + " L";
    }

    function calcularVolumenTotal() {
      var grano = parseFloat(document.getElementById("grano").value);
      var ratio = parseFloat(document.getElementById("ratio").value);

      if (isNaN(grano) || isNaN(ratio)) {
        document.getElementById("resultadoVolumenTotal").innerText = "Por favor completa el peso del grano y la relación agua/grano.";
        return;
      }

      var aguaTotal = grano * ratio;
      var absorcion = grano * 0.8; // Aproximadamente 0.8 L/kg de absorción
      var volumenFinal = aguaTotal - absorcion;
      document.getElementById("resultadoVolumenTotal").innerText = "Volumen estimado final del macerado: " + volumenFinal.toFixed(2) + " L (después de absorción)";
    }
  </script>