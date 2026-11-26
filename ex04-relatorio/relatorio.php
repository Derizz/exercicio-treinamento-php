<?php
declare(strict_types=1);

$produtos = [
    ['nome' => 'Cidre',           'peso' => 0.75, 'preco' => 18.90, 'estoque' => 24, 'ativo' => true],
    ['nome' => 'Jus de pomme',    'peso' => 1.00, 'preco' => 12.50, 'estoque' => 0,  'ativo' => true],
    ['nome' => 'Vinaigre',        'peso' => 0.50, 'preco' => 9.80,  'estoque' => 15, 'ativo' => false],
    ['nome' => 'Confiture',       'peso' => 0.35, 'preco' => 14.00, 'estoque' => 40, 'ativo' => true],
    ['nome' => 'Moutarde',        'peso' => 0.20, 'preco' => 7.50,  'estoque' => 5,  'ativo' => true],
    ['nome' => 'Miel de lavande', 'peso' => 0.50, 'preco' => 32.00, 'estoque' => 8,  'ativo' => true],
    ['nome' => "Huile d'olive",   'peso' => 1.00, 'preco' => 45.90, 'estoque' => 3,  'ativo' => false],
    ['nome' => 'Épices',          'peso' => 0.10, 'preco' => 6.90,  'estoque' => 60, 'ativo' => true],
    ['nome' => 'Pâté',            'peso' => 0.30, 'preco' => 21.00, 'estoque' => 0,  'ativo' => true],
    ['nome' => 'Tisane',          'peso' => 0.15, 'preco' => 11.30, 'estoque' => 18, 'ativo' => true],
];

function situacaoDoEstoque(int $estoque): string
{
    return match (true) {
        $estoque <= 0 => 'Esgotado',
        $estoque <= 9 => 'Baixo',
        $estoque >= 10 => 'Normal',
        default => 'Normal',
    };
}

function resumirProdutos(array $produtos): array
{
    $ativos = 0;
    $inativos = 0;
    $valorEstoque = 0.0;
    $pesoEstoque = 0.0;
    $somaPrecos = 0.0;
    $maisCaro = null;
    $maisBarato = null;
    $porSituacao = [
        'Esgotado' => 0,
        'Baixo' => 0,
        'Normal' => 0,
    ];
    $primeiroUrgente = null;

    foreach ($produtos as $produto) {
        if ($produto['ativo'] === false) {
            $inativos++;
            continue;
        }

        $ativos++;
        $preco = (float) $produto['preco'];
        $peso = (float) $produto['peso'];
        $estoque = (int) $produto['estoque'];
        $nome = (string) $produto['nome'];

        $valorEstoque += $preco * $estoque;
        $pesoEstoque += $peso * $estoque;
        $somaPrecos += $preco;

        if ($maisCaro === null || $preco > $maisCaro['preco']) {
            $maisCaro = ['nome' => $nome, 'preco' => $preco];
        }

        if ($maisBarato === null || $preco < $maisBarato['preco']) {
            $maisBarato = ['nome' => $nome, 'preco' => $preco];
        }

        $situacao = situacaoDoEstoque($estoque);
        if ($situacao === 'Esgotado') {
            $porSituacao['Esgotado']++;
        } elseif ($situacao === 'Baixo') {
            $porSituacao['Baixo']++;
        } else {
            $porSituacao['Normal']++;
        }
    }

    foreach ($produtos as $produto) {
        if ($produto['ativo'] === false) {
            continue;
        }

        $estoque = (int) $produto['estoque'];
        $situacao = situacaoDoEstoque($estoque);

        if ($situacao === 'Esgotado' || $situacao === 'Baixo') {
            $primeiroUrgente = (string) $produto['nome'];
            break;
        }
    }

    $precoMedio = $ativos > 0 ? $somaPrecos / $ativos : null;

    return [
        'ativos' => $ativos,
        'inativos' => $inativos,
        'valorEstoque' => $valorEstoque,
        'pesoEstoque' => $pesoEstoque,
        'precoMedio' => $precoMedio,
        'maisCaro' => $maisCaro,
        'maisBarato' => $maisBarato,
        'porSituacao' => $porSituacao,
        'primeiroUrgente' => $primeiroUrgente,
    ];
}

function lotesParaReposicao(int $estoqueAtual, int $tamanhoDoLote, int $meta): array
{
    if ($tamanhoDoLote <= 0) {
        return [
            'lotes' => 0,
            'estoqueFinal' => $estoqueAtual,
        ];
    }

    $lotes = 0;
    $estoqueFinal = $estoqueAtual;

    while ($estoqueFinal < $meta) {
        $estoqueFinal += $tamanhoDoLote;
        $lotes++;
    }

    return [
        'lotes' => $lotes,
        'estoqueFinal' => $estoqueFinal,
    ];
}

function tabelaDeDescontos(float $preco, int $de, int $ate, int $passo): array
{
    if ($passo <= 0 || $de > $ate) {
        return [];
    }

    $linhas = [];

    for ($percentual = $de; $percentual <= $ate; $percentual += $passo) {
        $valorComDesconto = round($preco * (1 - ($percentual / 100)), 2);
        $linhas[] = [
            'percentual' => $percentual,
            'preco' => $valorComDesconto,
        ];
    }

    return $linhas;
}

function formatarMoeda(float $valor): string
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function formatarPeso(float $peso): string
{
    return number_format($peso, 1, ',', '.') . ' kg';
}

function encontrarEstoqueDoProduto(array $produtos, ?string $nomeBuscado): ?int
{
    if ($nomeBuscado === null) {
        return null;
    }

    foreach ($produtos as $produto) {
        if ($produto['ativo'] === true && (string) $produto['nome'] === $nomeBuscado) {
            return (int) $produto['estoque'];
        }
    }

    return null;
}

$resumo = resumirProdutos($produtos);
$estoqueUrgente = encontrarEstoqueDoProduto($produtos, $resumo['primeiroUrgente']);
$reposicao = $estoqueUrgente !== null ? lotesParaReposicao($estoqueUrgente, 12, 30) : null;
$descontosMaisCaro = $resumo['maisCaro'] !== null
    ? tabelaDeDescontos((float) $resumo['maisCaro']['preco'], 0, 25, 5)
    : [];


$tolerancia = 0.005;
$conjuntosTeste = [
    [
        'nome' => 'Lista vazia ([])',
        'dados' => [],
        'esperado' => [
            'ativos' => 0,
            'inativos' => 0,
            'valorEstoque' => 0.0,
            'pesoEstoque' => 0.0,
            'precoMedio' => null,
            'maisCaro' => null,
            'maisBarato' => null,
            'porSituacao' => ['Esgotado' => 0, 'Baixo' => 0, 'Normal' => 0],
            'primeiroUrgente' => null,
        ],
    ],
    [
        'nome' => "Só inativos (Vinaigre e Huile d'olive)",
        'dados' => [
            ['nome' => 'Vinaigre',      'peso' => 0.50, 'preco' => 9.80,  'estoque' => 15, 'ativo' => false],
            ['nome' => "Huile d'olive", 'peso' => 1.00, 'preco' => 45.90, 'estoque' => 3,  'ativo' => false],
        ],
        'esperado' => [
            'ativos' => 0,
            'inativos' => 2,
            'valorEstoque' => 0.0,
            'pesoEstoque' => 0.0,
            'precoMedio' => null,
            'maisCaro' => null,
            'maisBarato' => null,
            'porSituacao' => ['Esgotado' => 0, 'Baixo' => 0, 'Normal' => 0],
            'primeiroUrgente' => null,
        ],
    ],
    [
        'nome' => 'Um item (apenas Cidre)',
        'dados' => [
            ['nome' => 'Cidre', 'peso' => 0.75, 'preco' => 18.90, 'estoque' => 24, 'ativo' => true],
        ],
        'esperado' => [
            'ativos' => 1,
            'inativos' => 0,
            'valorEstoque' => 453.60,
            'pesoEstoque' => 18.0,
            'precoMedio' => 18.90,
            'maisCaro' => ['nome' => 'Cidre', 'preco' => 18.90],
            'maisBarato' => ['nome' => 'Cidre', 'preco' => 18.90],
            'porSituacao' => ['Esgotado' => 0, 'Baixo' => 0, 'Normal' => 1],
            'primeiroUrgente' => null,
        ],
    ],
    [
        'nome' => 'Empate de preço (Miel de lavande e Truffe)',
        'dados' => [
            ['nome' => 'Miel de lavande', 'peso' => 0.50, 'preco' => 32.00, 'estoque' => 8, 'ativo' => true],
            ['nome' => 'Truffe',          'peso' => 0.10, 'preco' => 32.00, 'estoque' => 4, 'ativo' => true],
        ],
        'esperado' => [
            'ativos' => 2,
            'inativos' => 0,
            'valorEstoque' => 384.00,
            'pesoEstoque' => 4.4,
            'precoMedio' => 32.00,
            'maisCaro' => ['nome' => 'Miel de lavande', 'preco' => 32.00],
            'maisBarato' => ['nome' => 'Miel de lavande', 'preco' => 32.00],
            'porSituacao' => ['Esgotado' => 0, 'Baixo' => 2, 'Normal' => 0],
            'primeiroUrgente' => 'Miel de lavande',
        ],
    ],
];

$resultadosTestes = [];
$testesPassaram = 0;

foreach ($conjuntosTeste as $cTeste) {
    $obt = resumirProdutos($cTeste['dados']);
    $esp = $cTeste['esperado'];

    $okExtremos = (
        ($obt['maisCaro'] === null && $esp['maisCaro'] === null)
        || (
            $obt['maisCaro'] !== null
            && $esp['maisCaro'] !== null
            && $obt['maisCaro']['nome'] === $esp['maisCaro']['nome']
            && abs($obt['maisCaro']['preco'] - $esp['maisCaro']['preco']) < $tolerancia
        )
    ) && (
        ($obt['maisBarato'] === null && $esp['maisBarato'] === null)
        || (
            $obt['maisBarato'] !== null
            && $esp['maisBarato'] !== null
            && $obt['maisBarato']['nome'] === $esp['maisBarato']['nome']
            && abs($obt['maisBarato']['preco'] - $esp['maisBarato']['preco']) < $tolerancia
        )
    );

    $okMedia = ($obt['precoMedio'] === null && $esp['precoMedio'] === null)
        || ($obt['precoMedio'] !== null && $esp['precoMedio'] !== null && abs($obt['precoMedio'] - $esp['precoMedio']) < $tolerancia);

    $passou = (
        $obt['ativos'] === $esp['ativos']
        && $obt['inativos'] === $esp['inativos']
        && abs($obt['valorEstoque'] - $esp['valorEstoque']) < $tolerancia
        && abs($obt['pesoEstoque'] - $esp['pesoEstoque']) < $tolerancia
        && $okMedia
        && $okExtremos
        && $obt['porSituacao']['Esgotado'] === $esp['porSituacao']['Esgotado']
        && $obt['porSituacao']['Baixo'] === $esp['porSituacao']['Baixo']
        && $obt['porSituacao']['Normal'] === $esp['porSituacao']['Normal']
        && $obt['primeiroUrgente'] === $esp['primeiroUrgente']
    );

    if ($passou) {
        $testesPassaram++;
    }

    $resultadosTestes[] = [
        'nome' => $cTeste['nome'],
        'esperado' => 'ativos=' . $esp['ativos'] . ', inativos=' . $esp['inativos'] . ', valor=' . formatarMoeda($esp['valorEstoque']) . ', peso=' . formatarPeso($esp['pesoEstoque']) . ', urgente=' . ($esp['primeiroUrgente'] ?? 'nulo'),
        'obtido' => 'ativos=' . $obt['ativos'] . ', inativos=' . $obt['inativos'] . ', valor=' . formatarMoeda($obt['valorEstoque']) . ', peso=' . formatarPeso($obt['pesoEstoque']) . ', urgente=' . ($obt['primeiroUrgente'] ?? 'nulo'),
        'passou' => $passou,
    ];
}

$casosLacos = [
    ['chamada' => 'lotesParaReposicao(0, 12, 30)',    'obtido' => lotesParaReposicao(0, 12, 30),   'lotes' => 3, 'final' => 36],
    ['chamada' => 'lotesParaReposicao(30, 12, 30)',   'obtido' => lotesParaReposicao(30, 12, 30),  'lotes' => 0, 'final' => 30],
    ['chamada' => 'lotesParaReposicao(29, 12, 30)',   'obtido' => lotesParaReposicao(29, 12, 30),  'lotes' => 1, 'final' => 41],
    ['chamada' => 'lotesParaReposicao(5, 10, 5)',     'obtido' => lotesParaReposicao(5, 10, 5),    'lotes' => 0, 'final' => 5],
    ['chamada' => 'lotesParaReposicao(0, 0, 30)',     'obtido' => lotesParaReposicao(0, 0, 30),    'lotes' => 0, 'final' => 0],
    ['chamada' => 'lotesParaReposicao(0, -5, 30)',    'obtido' => lotesParaReposicao(0, -5, 30),   'lotes' => 0, 'final' => 0],
];

foreach ($casosLacos as $casoLaco) {
    $passou = $casoLaco['obtido']['lotes'] === $casoLaco['lotes'] && $casoLaco['obtido']['estoqueFinal'] === $casoLaco['final'];
    if ($passou) {
        $testesPassaram++;
    }
    $resultadosTestes[] = [
        'nome' => $casoLaco['chamada'],
        'esperado' => $casoLaco['lotes'] . ' lote(s), estoque final ' . $casoLaco['final'],
        'obtido' => $casoLaco['obtido']['lotes'] . ' lote(s), estoque final ' . $casoLaco['obtido']['estoqueFinal'],
        'passou' => $passou,
    ];
}

$descA = tabelaDeDescontos(32.00, 0, 25, 5);
$descBOk = count($descA) === 6 && abs($descA[0]['preco'] - 32.00) < $tolerancia && abs($descA[5]['preco'] - 24.00) < $tolerancia;
if ($descBOk) {
    $testesPassaram++;
}
$resultadosTestes[] = [
    'nome' => 'tabelaDeDescontos(32.00, 0, 25, 5)',
    'esperado' => '6 linhas (0% = R$ 32,00 até 25% = R$ 24,00)',
    'obtido' => count($descA) . ' linhas',
    'passou' => $descBOk,
];

$descB = tabelaDeDescontos(100.0, 10, 10, 5);
$descBOk2 = count($descB) === 1 && $descB[0]['percentual'] === 10 && abs($descB[0]['preco'] - 90.00) < $tolerancia;
if ($descBOk2) {
    $testesPassaram++;
}
$resultadosTestes[] = [
    'nome' => 'tabelaDeDescontos(100.0, 10, 10, 5)',
    'esperado' => '1 linha (10% = R$ 90,00)',
    'obtido' => count($descB) . ' linha(s)',
    'passou' => $descBOk2,
];

$descC = tabelaDeDescontos(100.0, 0, 20, 0);
$descCOk = count($descC) === 0;
if ($descCOk) {
    $testesPassaram++;
}
$resultadosTestes[] = [
    'nome' => 'tabelaDeDescontos(100.0, 0, 20, 0)',
    'esperado' => '0 linhas (lista vazia)',
    'obtido' => count($descC) . ' linha(s)',
    'passou' => $descCOk,
];

$totalTestes = count($resultadosTestes);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exercício 4 — Relatório de Produtos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; line-height: 1.5; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        tr.inativo { background-color: #f9f9f9; color: #777; }
        .ok { background-color: #d4edda; color: #155724; font-weight: bold; }
        .falhou { background-color: #f8d7da; color: #721c24; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Relatório de Produtos — Loja Francesa</h1>

    <?php if (count($produtos) === 0): ?>
        <p>Nenhum produto cadastrado.</p>
    <?php else: ?>
        <h2>Catálogo de Produtos</h2>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Preço</th>
                    <th>Peso</th>
                    <th>Estoque</th>
                    <th>Situação</th>
                    <th>Valor em estoque</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $produto): ?>
                    <?php
                    $isAtivo = $produto['ativo'] === true;
                    $precoItem = (float) $produto['preco'];
                    $pesoItem = (float) $produto['peso'];
                    $estoqueItem = (int) $produto['estoque'];
                    $valorItemEstoque = $precoItem * $estoqueItem;
                    ?>
                    <tr class="<?= $isAtivo ? 'ativo' : 'inativo' ?>">
                        <td><?= htmlspecialchars((string) $produto['nome'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(formatarMoeda($precoItem), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(formatarPeso($pesoItem), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $estoqueItem, ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?php if ($isAtivo): ?>
                                <?= htmlspecialchars(situacaoDoEstoque($estoqueItem), ENT_QUOTES, 'UTF-8') ?>
                            <?php else: ?>
                                &mdash;
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($isAtivo): ?>
                                <?= htmlspecialchars(formatarMoeda($valorItemEstoque), ENT_QUOTES, 'UTF-8') ?>
                            <?php else: ?>
                                &mdash;
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Resumo do Estoque</h2>
        <?php if ($resumo['ativos'] === 0): ?>
            <p>Nenhum produto ativo.</p>
        <?php else: ?>
            <ul>
                <li>Produtos ativos: <?= htmlspecialchars((string) $resumo['ativos'], ENT_QUOTES, 'UTF-8') ?> · Inativos: <?= htmlspecialchars((string) $resumo['inativos'], ENT_QUOTES, 'UTF-8') ?></li>
                <li>Valor total do estoque: <?= htmlspecialchars(formatarMoeda((float) $resumo['valorEstoque']), ENT_QUOTES, 'UTF-8') ?></li>
                <li>Peso total do estoque: <?= htmlspecialchars(formatarPeso((float) $resumo['pesoEstoque']), ENT_QUOTES, 'UTF-8') ?></li>
                <li>Preço médio dos ativos: <?= htmlspecialchars(formatarMoeda((float) $resumo['precoMedio']), ENT_QUOTES, 'UTF-8') ?></li>
                <li>Produto mais caro: <?= htmlspecialchars((string) $resumo['maisCaro']['nome'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars(formatarMoeda((float) $resumo['maisCaro']['preco']), ENT_QUOTES, 'UTF-8') ?>)</li>
                <li>Produto mais barato: <?= htmlspecialchars((string) $resumo['maisBarato']['nome'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars(formatarMoeda((float) $resumo['maisBarato']['preco']), ENT_QUOTES, 'UTF-8') ?>)</li>
            </ul>

            <h3>Contagem por situação</h3>
            <ul>
                <?php foreach ($resumo['porSituacao'] as $rotuloSituacao => $qtdSituacao): ?>
                    <li><?= htmlspecialchars((string) $rotuloSituacao, ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars((string) $qtdSituacao, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>

            <h2>Reposição Urgente</h2>
            <?php if ($resumo['primeiroUrgente'] !== null && $reposicao !== null): ?>
                <p>
                    Primeiro produto a repor: <?= htmlspecialchars((string) $resumo['primeiroUrgente'], ENT_QUOTES, 'UTF-8') ?>.
                    Com lotes de 12 unidades, são necessários <?= htmlspecialchars((string) $reposicao['lotes'], ENT_QUOTES, 'UTF-8') ?> lotes para chegar a pelo menos 30 unidades (estoque final: <?= htmlspecialchars((string) $reposicao['estoqueFinal'], ENT_QUOTES, 'UTF-8') ?>).
                </p>
            <?php else: ?>
                <p>Nenhum produto precisa de reposição.</p>
            <?php endif; ?>

            <h2>Simulação de Descontos — <?= htmlspecialchars((string) $resumo['maisCaro']['nome'], ENT_QUOTES, 'UTF-8') ?></h2>
            <table>
                <thead>
                    <tr>
                        <th>Desconto</th>
                        <th>Preço final</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($descontosMaisCaro as $linhaDesconto): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $linhaDesconto['percentual'], ENT_QUOTES, 'UTF-8') ?>%</td>
                            <td><?= htmlspecialchars(formatarMoeda((float) $linhaDesconto['preco']), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endif; ?>

    <h2>Testes</h2>
    <p><strong><?= htmlspecialchars((string) $testesPassaram, ENT_QUOTES, 'UTF-8') ?> de <?= htmlspecialchars((string) $totalTestes, ENT_QUOTES, 'UTF-8') ?> testes passaram</strong></p>
    <table>
        <thead>
            <tr>
                <th>Cenário / Chamada</th>
                <th>Esperado</th>
                <th>Obtido</th>
                <th>Resultado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resultadosTestes as $teste): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $teste['nome'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) $teste['esperado'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) $teste['obtido'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="<?= $teste['passou'] ? 'ok' : 'falhou' ?>">
                        <?= $teste['passou'] ? 'OK' : 'FALHOU' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
