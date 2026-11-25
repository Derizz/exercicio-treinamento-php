<?php
$loja = "Café & Cia";
$item1Nome = "Caixa de papelão 30x20";
$item1Preco = 4.90;
$item1Qtd = 30;
$item1Peso = 0.12;

$item2Nome = "Fita adesiva48 mm";
$item2Preco = 7.35;
$item2Qtd = 6;
$item2Peso = 0.25;

$item3Nome = "Plástico-bolha (rolo)";
$item3Preco = 39.90;
$item3Qtd = 2;
$item3Peso = 1.80;

const DESCONTO_PERCENTUAL = 8;
const FRETE = 18.50;
const PARCELAS = 3;
const UNIDADES_POR_DUZIA = 12;
const CASAS_DECIMAIS = 2;


$item1Subtotal = $item1Preco * $item1Qtd; 
$item2Subtotal = $item2Preco * $item2Qtd; 
$item3Subtotal = $item3Preco * $item3Qtd;
$subtotalGeral = $item1Subtotal + $item2Subtotal + $item3Subtotal; 

$valorDesconto = $subtotalGeral * DESCONTO_PERCENTUAL / 100;
$subtotalComDesconto = $subtotalGeral - $valorDesconto; 


$freteExibido = FRETE;

$total = $subtotalComDesconto + FRETE;

$quantidadeTotal = $item1Qtd + $item2Qtd + $item3Qtd; 
$pesoTotal = ($item1Peso * $item1Qtd) + ($item2Peso * $item2Qtd) + ($item3Peso * $item3Qtd); // 8.7
$precoMedio = $subtotalGeral / $quantidadeTotal; 


$item1Duzias = intdiv($item1Qtd, UNIDADES_POR_DUZIA); 
$item1Soltas = $item1Qtd % UNIDADES_POR_DUZIA; 

$totalCentavos = (int) round($total * 100);

$parcelaBaseCentavos = intdiv($totalCentavos, PARCELAS);
$restoCentavos = $totalCentavos % PARCELAS;  

$primeiraParcelaCentavos = $parcelaBaseCentavos + $restoCentavos;
$demaisParcelasCentavos = $parcelaBaseCentavos;
$qtdDemaisParcelas = PARCELAS - 1;

$primeiraParcelaReais = $primeiraParcelaCentavos / 100;
$demaisParcelasReais = $demaisParcelasCentavos / 100; 


$somaParcelasCentavos = $primeiraParcelaCentavos + ($demaisParcelasCentavos * $qtdDemaisParcelas);
$conferenciaCentavos = ($somaParcelasCentavos === $totalCentavos); 


$clienteEscapado = htmlspecialchars($loja, ENT_QUOTES, 'UTF-8');
$pesoFormatado = number_format($pesoTotal, 1, ',', '.');
$parcelasTotal = PARCELAS;

$resumoTexto = <<<TEXTO
Pedido de {$clienteEscapado} com {$quantidadeTotal} itens, pesando {$pesoFormatado} kg, em {$parcelasTotal} parcelas.
TEXTO;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Orçamento — <?= $clienteEscapado ?></title>
</head>
<body>
  <h1>Orçamento de Pedido</h1>
  <p><strong>Cliente:</strong> <?= $clienteEscapado ?></p>

  <table>
    <thead>
      <tr>
        <th>Item</th>
        <th>Preço unitário</th>
        <th>Quantidade</th>
        <th>Subtotal</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><?= htmlspecialchars($item1Nome, ENT_QUOTES, 'UTF-8') ?></td>
        <td>R$ <?= number_format($item1Preco, CASAS_DECIMAIS, ',', '.') ?></td>
        <td><?= $item1Qtd ?></td>
        <td>R$ <?= number_format($item1Subtotal, CASAS_DECIMAIS, ',', '.') ?></td>
      </tr>
      <tr>
        <td><?= htmlspecialchars($item2Nome, ENT_QUOTES, 'UTF-8') ?></td>
        <td>R$ <?= number_format($item2Preco, CASAS_DECIMAIS, ',', '.') ?></td>
        <td><?= $item2Qtd ?></td>
        <td>R$ <?= number_format($item2Subtotal, CASAS_DECIMAIS, ',', '.') ?></td>
      </tr>
      <tr>
        <td><?= htmlspecialchars($item3Nome, ENT_QUOTES, 'UTF-8') ?></td>
        <td>R$ <?= number_format($item3Preco, CASAS_DECIMAIS, ',', '.') ?></td>
        <td><?= $item3Qtd ?></td>
        <td>R$ <?= number_format($item3Subtotal, CASAS_DECIMAIS, ',', '.') ?></td>
      </tr>
    </tbody>
  </table>

  <h2>Resumo do pedido</h2>
  <p>Subtotal geral: R$ <?= number_format($subtotalGeral, CASAS_DECIMAIS, ',', '.') ?></p>
  <p>Desconto (<?= DESCONTO_PERCENTUAL ?>%): R$ <?= number_format($valorDesconto, CASAS_DECIMAIS, ',', '.') ?></p>
  <p>Subtotal com desconto: R$ <?= number_format($subtotalComDesconto, CASAS_DECIMAIS, ',', '.') ?></p>
  <p>Frete: R$ <?= number_format($freteExibido, CASAS_DECIMAIS, ',', '.') ?></p>
  <p>Total: R$ <?= number_format($total, CASAS_DECIMAIS, ',', '.') ?></p>
  <p>Quantidade total de itens: <?= $quantidadeTotal ?></p>
  <p>Peso total: <?= $pesoFormatado ?> kg</p>
  <p>Preço médio por item: R$ <?= number_format($precoMedio, CASAS_DECIMAIS, ',', '.') ?></p>
  <p>Item 1 em dúzias: <?= $item1Duzias ?> dúzias completas e <?= $item1Soltas ?> soltas</p>

  <h2>Parcelamento em centavos</h2>
  <p>Total em centavos: <?= $totalCentavos ?></p>
  <p>Parcela base · resto: <?= $parcelaBaseCentavos ?> · <?= $restoCentavos ?></p>
  <p>1ª parcela: R$ <?= number_format($primeiraParcelaReais, CASAS_DECIMAIS, ',', '.') ?></p>
  <p>Demais <?= $qtdDemaisParcelas ?> parcelas: R$ <?= number_format($demaisParcelasReais, CASAS_DECIMAIS, ',', '.') ?> cada</p>
  <pre><?php var_dump($conferenciaCentavos); ?></pre>

  <p><?= $resumoTexto ?></p>


  
  <h2>Laboratório de números</h2>
  <pre><?php
var_dump(4.90 * 30);
var_dump(0.1 + 0.2 == 0.3);
var_dump((int) (19.99 * 100));
var_dump((int) round(19.99 * 100));
var_dump((int) (267.728 * 100));
var_dump((int) round(267.728 * 100));
var_dump(intdiv(26773, 3));
var_dump(26773 % 3);
var_dump(10 / 4);
var_dump(10 / 5);
var_dump(10 % 4);
  ?></pre>
```[cite: 9]

### 2. O que observar e explicar no `LEIAME.txt`

* **L1 (`4.90 * 30`):** multiplicação entre `float` e `int` produzindo `float(147)` — mostra que o resultado numérico é um número decimal/flutuante antes de aplicar `number_format(..., 2, ',', '.')` para exibir `R$ 147,00`[cite: 9].
* **L2 (`0.1 + 0.2 == 0.3`):** imprecisão de ponto flutuante (`float` em binário)[cite: 9]. Nem todo decimal exato na base 10 (`0.1`, `0.2`, `0.3`) tem representação binária finita, então `0.1 + 0.2` pode não ser exatamente igual a `0.3`, resultando em `bool(false)` (além de usar comparação frouxa `==` em vez de `===`)[cite: 9].
* **L3 (`(int) (19.99 * 100)`):** conversão explícita para `int` (truncamento)[cite: 9]. Em ponto flutuante binário, `19.99 * 100` pode ficar ligeiramente abaixo de `1999` (por exemplo, `1998.9999999999998`), e o cast `(int)` corta a parte decimal sem arredondar, podendo dar `1998` em vez de `1999`[cite: 9].
* **L4 (`(int) round(19.99 * 100)`):** `round()` arredonda o `float` para o inteiro mais próximo antes do cast `(int)`, corrigindo pequenas variações de representação binária e devolvendo `int(1999)`[cite: 9].
* **L5 (`(int) (267.728 * 100)` e `(int) round(267.728 * 100)`):** `267.728 * 100` é `26772.8`[cite: 9]. O cast `(int)` trunca as casas decimais e vira `26772` (perdendo `0.8` do centavo), enquanto `(int) round(...)` arredonda para `int(26773)`[cite: 9].
* **L6 (`intdiv(26773, 3)` e `26773 % 3`):** divisão inteira e resto da divisão entre inteiros[cite: 9]. `intdiv(26773, 3)` retorna `int(8924)` (quantos centavos cabem inteiros em cada uma das 3 parcelas) e `26773 % 3` retorna `int(1)` (o centavo restante)[cite: 9].
* **L7 (`10 / 4`, `10 / 5` e `10 % 4`):** diferença entre divisão comum (`/`) e resto de divisão inteira (`%`)[cite: 9]. `10 / 4` resulta em `float(2.5)`, `10 / 5` em `float(2)` (no PHP, `/` sempre retorna `float`, mesmo quando a divisão é exata), e `10 % 4` em `int(2)`[cite: 9].

### 3. Por que parcelar em centavos inteiros em vez de dividir o `float` por 3 e arredondar?
Se você dividir um `float` por `3` e arredondar cada parcela separadamente, o arredondamento em cada parcela pode somar um valor diferente do total original (perdendo ou ganhando centavos por causa de arredondamentos repetidos e da imprecisão de ponto flutuante)[cite: 9]. Trabalhando com centavos inteiros (`int`), usando `intdiv` para a parcela base e `%` para o resto, você distribui o resto exato (por exemplo, na 1ª parcela) e garante que a soma de todas as parcelas em centavos seja rigorosamente igual ao total em centavos (`bool(true)`)[cite: 9].

---

### Como você quer continuar?

<ElicitationsGroup message="Como você quer avançar agora no Exercício 1?">
  <Elicitation label="Testar o Conjunto B e a falha com qtd 0" query="Vamos fazer o teste de robustez do exercício 1 com o conjunto B e registrar a falha esperada com quantidades iguais a 0."/>
  <Elicitation label="Montar o LEIAME.txt do ex01-orcamento" query="Ajude a escrever o conteúdo do LEIAME.txt do ex01-orcamento com o laboratório de números, decisões e respostas."/>
  <Elicitation label="Conferir a lista de verificação final" query="Vamos revisar a lista de verificação antes de entregar e fazer os três commits do ex01-orcamento."/>
</ElicitationsGroup>
</body>
</html>
