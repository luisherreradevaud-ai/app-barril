<h2>Calculadora de Química de Agua para Maceración</h2>
 
  <div class="section">
    <label>Volumen de agua (L): <input type="number" id="volumen" value="25"></label>
  </div>

  <div class="section">
    <h3>Perfil del agua actual (ppm)</h3>
    <label>Ca: <input type="number" id="ca_actual" value="20"></label><br>
    <label>Mg: <input type="number" id="mg_actual" value="5"></label><br>
    <label>Na: <input type="number" id="na_actual" value="10"></label><br>
    <label>Cl: <input type="number" id="cl_actual" value="15"></label><br>
    <label>SO₄: <input type="number" id="so4_actual" value="10"></label>
  </div>

  <div class="section">
    <h3>Perfil objetivo (ppm)</h3>
    <label>Ca: <input type="number" id="ca_objetivo" value="80"></label><br>
    <label>Mg: <input type="number" id="mg_objetivo" value="10"></label><br>
    <label>Na: <input type="number" id="na_objetivo" value="20"></label><br>
    <label>Cl: <input type="number" id="cl_objetivo" value="50"></label><br>
    <label>SO₄: <input type="number" id="so4_objetivo" value="100"></label>
  </div>

  <button onclick="calcularSales()">Calcular Ajustes de Sales</button>

  <div class="section">
    <h3>Resultado</h3>
    <pre id="resultado"></pre>
  </div>

  <script>
    const sales = {
      gypsum: { Ca: 61.5, SO4: 147.4 },
      cacl2: { Ca: 72, Cl: 127 },
      epsom: { Mg: 26, SO4: 103 },
      baking: { Na: 72 },
      salt: { Na: 60, Cl: 90 }
    };

    function calcularSales() {
      const vol_L = parseFloat(document.getElementById('volumen').value);
      const factor = vol_L / 3.785;

      const actual = {
        Ca: parseFloat(document.getElementById('ca_actual').value),
        Mg: parseFloat(document.getElementById('mg_actual').value),
        Na: parseFloat(document.getElementById('na_actual').value),
        Cl: parseFloat(document.getElementById('cl_actual').value),
        SO4: parseFloat(document.getElementById('so4_actual').value)
      };

      const objetivo = {
        Ca: parseFloat(document.getElementById('ca_objetivo').value),
        Mg: parseFloat(document.getElementById('mg_objetivo').value),
        Na: parseFloat(document.getElementById('na_objetivo').value),
        Cl: parseFloat(document.getElementById('cl_objetivo').value),
        SO4: parseFloat(document.getElementById('so4_objetivo').value)
      };

      const dif = {
        Ca: objetivo.Ca - actual.Ca,
        Mg: objetivo.Mg - actual.Mg,
        Na: objetivo.Na - actual.Na,
        Cl: objetivo.Cl - actual.Cl,
        SO4: objetivo.SO4 - actual.SO4
      };

      // Aproximación simple (sin solver)
      let gypsum_g = Math.max(0, dif.Ca / sales.gypsum.Ca, dif.SO4 / sales.gypsum.SO4);
      let cacl2_g = Math.max(0, (dif.Cl - sales.gypsum.SO4 * gypsum_g / sales.gypsum.SO4) / sales.cacl2.Cl);
      let epsom_g = Math.max(0, dif.Mg / sales.epsom.Mg);
      let baking_g = Math.max(0, (dif.Na - (sales.salt.Na * 0)) / sales.baking.Na); // no sal aún
      let salt_g = Math.max(0, (dif.Na - baking_g * sales.baking.Na) / sales.salt.Na);

      // Escalar a volumen ingresado
      gypsum_g *= factor;
      cacl2_g *= factor;
      epsom_g *= factor;
      baking_g *= factor;
      salt_g *= factor;

      const resultado = `
        (CaSO₄): ${gypsum_g.toFixed(2)} g
        Cloruro de calcio (CaCl₂): ${cacl2_g.toFixed(2)} g
        Sulfato de magnesio (MgSO₄): ${epsom_g.toFixed(2)} g
        Bicarbonato de sodio (NaHCO₃): ${baking_g.toFixed(2)} g
        Sal (NaCl): ${salt_g.toFixed(2)} g
      `;

      document.getElementById('resultado').textContent = resultado;
    }
  </script>