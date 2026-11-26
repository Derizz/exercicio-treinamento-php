<?php
declare(strict_types=1);

const PESO_MAXIMO_KG = 30.0;
const MULTIPLICADOR_EXPRESSO = 1.40;
const LIMITE_FRETE_GRATIS_PADRAO = 300.0;
const LIMITE_FRETE_GRATIS_NORTE = 500.0;
const TOLERANCIA_VALOR = 0.005;

function normalizarRegiao(string $regiao): string
{
    return mb_strtolower(trim($regiao), 'UTF-8');
}

function valorBasePorPeso(float $peso): ?float
{
    if ($peso <= 0.0 || $peso > PESO_MAXIMO_KG) {
        return null;
    }

    return match (true) {
        $peso <= 1.0 => 12.0,
        $peso <= 5.0 => 20.0,
        $peso <= 10.0 => 32.0,
        $peso <= PESO_MAXIMO_KG => 55.0,
        default => null,
    };
}

function multiplicadorDaRegiao(string $regiao): ?float
{
    $normalizada = normalizarRegiao($regiao);

    return match ($normalizada) {
        'sudeste' => 1.00,
        'sul' => 1.10,
        'centro-oeste' => 1.25,
        'nordeste' => 1.40,
        'norte' => 1.60,
        default => null,
    };
}

function prazoDaRegiao(string $regiao): int
{
    $normalizada = normalizarRegiao($regiao);

    return match ($normalizada) {
        'sudeste' => 2,
        'sul' => 3,
        'centro-oeste' => 4,
        'nordeste' => 6,
        'norte' => 9,
        default => 0,
    };
}

function limiteFreteGratis(string $regiao): float
{
    $normalizada = normalizarRegiao($regiao);

    return $normalizada === 'norte'
        ? LIMITE_FRETE_GRATIS_NORTE
        : LIMITE_FRETE_GRATIS_PADRAO;
}

function descreverPrazo(int $dias): string
{
    return $dias === 1 ? '1 dia útil' : $dias . ' dias úteis';
}

function calcularFrete(float $peso, string $regiao, float $valorPedido, bool $expresso = false): array
{
    $basePeso = valorBasePorPeso($peso);
    if ($basePeso === null) {
        return [
            'ok' => false,
            'valor' => 0.0,
            'prazo' => null,
            'gratis' => false,
            'mensagem' => 'Peso inválido: informe mais de 0 e até 30 kg.',
        ];
    }

    $multiplicador = multiplicadorDaRegiao($regiao);
    if ($multiplicador === null) {
        return [
            'ok' => false,
            'valor' => 0.0,
            'prazo' => null,
            'gratis' => false,
            'mensagem' => 'Região desconhecida: "' . $regiao . '".',
        ];
    }

    if ($valorPedido < 0.0) {
        return [
            'ok' => false,
            'valor' => 0.0,
            'prazo' => null,
            'gratis' => false,
            'mensagem' => 'O valor do pedido não pode ser negativo.',
        ];
    }

    $prazoBase = prazoDaRegiao($regiao);
    $prazoFinal = $expresso ? max(1, (int) ceil($prazoBase / 2)) : $prazoBase;
    $limiteGratis = limiteFreteGratis($regiao);

    if (!$expresso && $valorPedido >= $limiteGratis) {
        return [
            'ok' => true,
            'valor' => 0.0,
            'prazo' => $prazoFinal,
            'gratis' => true,
            'mensagem' => 'Frete grátis! Entrega em até ' . descreverPrazo($prazoFinal) . '.',
        ];
    }

    $valorCalculado = $basePeso * $multiplicador;
    if ($expresso) {
        $valorCalculado *= MULTIPLICADOR_EXPRESSO;
    }

    $valorFinal = round($valorCalculado, 2);
    $regiaoFormatada = normalizarRegiao($regiao);
    $valorFormatado = 'R$ ' . number_format($valorFinal, 2, ',', '.');
    $textoPrazo = descreverPrazo($prazoFinal);

    $mensagem = 'Frete de ' . $valorFormatado . ' para a região ' . $regiaoFormatada . ', em até ' . $textoPrazo . '.';
    if ($expresso) {
        $mensagem .= ' (expresso)';
    }

    return [
        'ok' => true,
        'valor' => $valorFinal,
        'prazo' => $prazoFinal,
        'gratis' => false,
        'mensagem' => $mensagem,
    ];
}

$casosTeste = [
    ['n' => 1,  'peso' => 0.5,   'regiao' => 'sudeste',      'pedido' => 100.0,  'expresso' => false, 'ok' => true,  'valor' => 12.00,  'prazo' => 2,    'gratis' => false, 'campoErro' => null],
    ['n' => 2,  'peso' => 1.0,   'regiao' => 'sudeste',      'pedido' => 100.0,  'expresso' => false, 'ok' => true,  'valor' => 12.00,  'prazo' => 2,    'gratis' => false, 'campoErro' => null],
    ['n' => 3,  'peso' => 1.01,  'regiao' => 'sudeste',      'pedido' => 100.0,  'expresso' => false, 'ok' => true,  'valor' => 20.00,  'prazo' => 2,    'gratis' => false, 'campoErro' => null],
    ['n' => 4,  'peso' => 5.0,   'regiao' => 'sul',          'pedido' => 100.0,  'expresso' => false, 'ok' => true,  'valor' => 22.00,  'prazo' => 3,    'gratis' => false, 'campoErro' => null],
    ['n' => 5,  'peso' => 5.01,  'regiao' => 'sul',          'pedido' => 100.0,  'expresso' => false, 'ok' => true,  'valor' => 35.20,  'prazo' => 3,    'gratis' => false, 'campoErro' => null],
    ['n' => 6,  'peso' => 10.0,  'regiao' => 'nordeste',     'pedido' => 100.0,  'expresso' => false, 'ok' => true,  'valor' => 44.80,  'prazo' => 6,    'gratis' => false, 'campoErro' => null],
    ['n' => 7,  'peso' => 10.01, 'regiao' => 'nordeste',     'pedido' => 100.0,  'expresso' => false, 'ok' => true,  'valor' => 77.00,  'prazo' => 6,    'gratis' => false, 'campoErro' => null],
    ['n' => 8,  'peso' => 30.0,  'regiao' => 'norte',        'pedido' => 100.0,  'expresso' => false, 'ok' => true,  'valor' => 88.00,  'prazo' => 9,    'gratis' => false, 'campoErro' => null],
    ['n' => 9,  'peso' => 30.01, 'regiao' => 'sudeste',      'pedido' => 100.0,  'expresso' => false, 'ok' => false, 'valor' => 0.00,   'prazo' => null, 'gratis' => false, 'campoErro' => 'Peso'],
    ['n' => 10, 'peso' => 0.0,   'regiao' => 'sul',          'pedido' => 100.0,  'expresso' => false, 'ok' => false, 'valor' => 0.00,   'prazo' => null, 'gratis' => false, 'campoErro' => 'Peso'],
    ['n' => 11, 'peso' => -2.0,  'regiao' => 'sul',          'pedido' => 100.0,  'expresso' => false, 'ok' => false, 'valor' => 0.00,   'prazo' => null, 'gratis' => false, 'campoErro' => 'Peso'],
    ['n' => 12, 'peso' => 2.0,   'regiao' => 'lua',          'pedido' => 100.0,  'expresso' => false, 'ok' => false, 'valor' => 0.00,   'prazo' => null, 'gratis' => false, 'campoErro' => 'Região'],
    ['n' => 13, 'peso' => 2.0,   'regiao' => 'sudeste',      'pedido' => 300.0,  'expresso' => false, 'ok' => true,  'valor' => 0.00,   'prazo' => 2,    'gratis' => true,  'campoErro' => null],
    ['n' => 14, 'peso' => 2.0,   'regiao' => 'sudeste',      'pedido' => 299.99, 'expresso' => false, 'ok' => true,  'valor' => 20.00,  'prazo' => 2,    'gratis' => false, 'campoErro' => null],
    ['n' => 15, 'peso' => 2.0,   'regiao' => 'norte',        'pedido' => 300.0,  'expresso' => false, 'ok' => true,  'valor' => 32.00,  'prazo' => 9,    'gratis' => false, 'campoErro' => null],
    ['n' => 16, 'peso' => 2.0,   'regiao' => 'norte',        'pedido' => 500.0,  'expresso' => false, 'ok' => true,  'valor' => 0.00,   'prazo' => 9,    'gratis' => true,  'campoErro' => null],
    ['n' => 17, 'peso' => 2.0,   'regiao' => 'sudeste',      'pedido' => 400.0,  'expresso' => true,  'ok' => true,  'valor' => 28.00,  'prazo' => 1,    'gratis' => false, 'campoErro' => null],
    ['n' => 18, 'peso' => 7.0,   'regiao' => 'centro-oeste', 'pedido' => 50.0,   'expresso' => true,  'ok' => true,  'valor' => 56.00,  'prazo' => 2,    'gratis' => false, 'campoErro' => null],
    ['n' => 19, 'peso' => 15.0,  'regiao' => 'nordeste',     'pedido' => 80.0,   'expresso' => true,  'ok' => true,  'valor' => 107.80, 'prazo' => 3,    'gratis' => false, 'campoErro' => null],
    ['n' => 20, 'peso' => 4.0,   'regiao' => 'sul',          'pedido' => -10.0,  'expresso' => false, 'ok' => false, 'valor' => 0.00,   'prazo' => null, 'gratis' => false, 'campoErro' => 'pedido'],
    ['n' => 21, 'peso' => 2.0,   'regiao' => ' Sul ',        'pedido' => 100.0,  'expresso' => false, 'ok' => true,  'valor' => 22.00,  'prazo' => 3,    'gratis' => false, 'campoErro' => null],
    ['n' => 22, 'peso' => 25.0,  'regiao' => 'norte',        'pedido' => 600.0,  'expresso' => false, 'ok' => true,  'valor' => 0.00,   'prazo' => 9,    'gratis' => true,  'campoErro' => null],
    ['n' => 23, 'peso' => 0.001, 'regiao' => 'sul',          'pedido' => 0.0,    'expresso' => false, 'ok' => true,  'valor' => 13.20,  'prazo' => 3,    'gratis' => false, 'campoErro' => null],
];

$simulacoes = [
    ['peso' => 1.0,  'regiao' => 'sudeste',      'pedido' => 120.0, 'expresso' => false],
    ['peso' => 4.5,  'regiao' => 'sul',          'pedido' => 350.0, 'expresso' => false],
    ['peso' => 8.0,  'regiao' => 'centro-oeste', 'pedido' => 90.0,  'expresso' => true],
    ['peso' => 2.0,  'regiao' => 'lua',          'pedido' => 150.0, 'expresso' => false],
    ['peso' => 12.0, 'regiao' => 'norte',        'pedido' => 520.0, 'expresso' => false],
];

$resultados = [];
$passaram = 0;

foreach ($casosTeste as $caso) {
    $obtido = calcularFrete($caso['peso'], $caso['regiao'], $caso['pedido'], $caso['expresso']);
    $valorOk = abs($obtido['valor'] - $caso['valor']) < TOLERANCIA_VALOR;
    $mensagemOk = $caso['campoErro'] === null
        ? true
        : mb_stripos($obtido['mensagem'], $caso['campoErro'], 0, 'UTF-8') !== false;

    $passou = (
        $obtido['ok'] === $caso['ok']
        && $valorOk
        && $obtido['prazo'] === $caso['prazo']
        && $obtido['gratis'] === $caso['gratis']
        && $mensagemOk
    );

    if ($passou) {
        $passaram++;
    }

    $resultados[] = [
        'caso' => $caso,
        'obtido' => $obtido,
        'passou' => $passou,
    ];
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exercício 3 — Calculadora de Frete</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; line-height: 1.5; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 24px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f4f4f4; }
        .ok { color: #155724; background: #d4edda; font-weight: bold; }
        .falhou { color: #721c24; background: #f8d7da; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Calculadora de Frete — Bateria de Testes</h1>
    <p><strong><?= htmlspecialchars((string) $passaram, ENT_QUOTES, 'UTF-8') ?> de <?= htmlspecialchars((string) count($resultados), ENT_QUOTES, 'UTF-8') ?> testes passaram</strong></p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Entrada</th>
                <th>Esperado</th>
                <th>Obtido</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            
            <?php foreach ($resultados as $item): ?>
                <?php
                $c = $item['caso'];
                $o = $item['obtido'];
                $entradaTexto = 'peso=' . $c['peso'] . ', regiao="' . $c['regiao'] . '", pedido=' . $c['pedido'] . ', expresso=' . ($c['expresso'] ? 'sim' : 'não');
                $esperadoTexto = 'ok=' . ($c['ok'] ? 'sim' : 'não') . ', valor=' . number_format($c['valor'], 2, ',', '.') . ', prazo=' . ($c['prazo'] === null ? 'nulo' : (string) $c['prazo']) . ', gratis=' . ($c['gratis'] ? 'sim' : 'não');
                $obtidoTexto = 'ok=' . ($o['ok'] ? 'sim' : 'não') . ', valor=' . number_format($o['valor'], 2, ',', '.') . ', prazo=' . ($o['prazo'] === null ? 'nulo' : (string) $o['prazo']) . ', gratis=' . ($o['gratis'] ? 'sim' : 'não') . ' | ' . $o['mensagem'];
                ?>
                <tr>
                    <td><?= (int) $c['n'] ?></td>
                    <td><?= htmlspecialchars($entradaTexto, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($esperadoTexto, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($obtidoTexto, ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="<?= $item['passou'] ? 'ok' : 'falhou' ?>">
                        <?= $item['passou'] ? 'OK' : 'FALHOU' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Simulador de Pedidos</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Pedido</th>
                <th>Mensagem</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($simulacoes as $indice => $sim): ?>
                <?php $resSim = calcularFrete($sim['peso'], $sim['regiao'], $sim['pedido'], $sim['expresso']); ?>
                <tr>
                    <td><?= (int) ($indice + 1) ?></td>
                    <td><?= htmlspecialchars('peso=' . $sim['peso'] . ', regiao="' . $sim['regiao'] . '", pedido=' . $sim['pedido'] . ', expresso=' . ($sim['expresso'] ? 'sim' : 'não'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($resSim['mensagem'], ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
