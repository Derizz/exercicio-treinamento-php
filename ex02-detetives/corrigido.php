<?php
// bugs.php: este arquivo tem 11 defeitos. Encontre, explique e corrija cada um.

$produto = "Jus de pomme biologique";
$Marca = "Green Orchard";
$descricao = "Pressé & <b>100% naturel</b>";
$itens = 0;
$soma = 17;
$unidades = 25;
$porPagina = 10;

$media = $itens > 0 ? $soma / $itens : null; 
$letrasPress = mb_strlen("pressé", "UTF-8"); 
$somaCorreta = 5 + 3 + 2; 
$paginasNecessarias = (int) ceil($unidades / $porPagina); 
$epsilon = 1e-9;
$somaFloatIgual = abs((0.1 + 0.2) - 0.3) < $epsilon; 

echo "<pre>";
echo "Produto: $produto\n";
echo "GTIN: $gtin\n"; 
echo "Marca: $Marca\n"; 
echo "Letras da palavra pressé: $letrasPress\n";
echo "Soma de 5, 3 e 2: $somaCorreta\n";
echo "Páginas necessárias: $paginasNecessarias\n";
echo "Média: " . ($media !== null ? $media : "sem itens para calcular") . "\n";
echo "0,1 + 0,2 é igual a 0,3? " . ($somaFloatIgual ? "sim" : "não") . "\n";
echo "</pre>"; 
echo "<p>" . htmlspecialchars($descricao, ENT_QUOTES, "UTF-8") . "</p>";