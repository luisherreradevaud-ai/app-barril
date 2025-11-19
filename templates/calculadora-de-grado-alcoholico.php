<?php

$litros_value = 0;
$graduacion_value = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $C1 = 96;
    $litros_value = floatval($_POST['litros_deseados']);
    $graduacion_value = floatval($_POST['grados_deseados']);
}

?>

<style>
    form {
        margin-bottom: 20px;
    }
    input[type=number] {
        padding: 5px;
        margin-bottom: 10px;
        width: 200px;
    }
    input[type=submit] {
        padding: 8px 15px;
    }
    .resultado {
        background-color: #fff;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 0 8px rgba(0,0,0,0.1);
    }
</style>
<h2>Calculadora para diluir alcohol desde 96°</h2>

    <form method="post">
        <label for="litros_deseados">Litros finales deseados (diluidos):</label><br>
        <input type="number" step="0.01" name="litros_deseados" value="<?= $litros_value; ?>" required><br>

        <label for="grados_deseados">Graduación alcohólica deseada (°):</label><br>
        <input type="number" step="0.1" name="grados_deseados" min="1" max="96" value="<?= $graduacion_value; ?>" required><br><br>

        <input type="submit" value="Calcular">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $C1 = 96; // Grado del alcohol puro
        $V2 = floatval($_POST['litros_deseados']); // Volumen diluido deseado
        $C2 = floatval($_POST['grados_deseados']); // Graduación final deseada

        if ($C2 >= $C1 || $C2 <= 0 || $V2 <= 0) {
            echo "<div class='resultado'><strong>Error:</strong> Verifica que la graduación deseada sea menor a $C1° y mayor que 0°, y que el volumen sea mayor que 0.</div>";
        } else {
            $V1 = ($C2 * $V2) / $C1;
            $agua = $V2 - $V1;

            echo "<div class='resultado'>";
            echo "<h3>Resultado:</h3>";
            echo "Para obtener <strong>" . round($V2, 2) . " litros</strong> a <strong>$C2 °</strong>:<br>";
            echo "→ Necesitas <strong>" . round($V1, 2) . " litros</strong> de alcohol al 96°.<br>";
            echo "→ Debes agregar <strong>" . round($agua, 2) . " litros</strong> de agua.";
            echo "</div>";
        }
    }
    ?>
