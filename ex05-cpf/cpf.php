<?php

declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

function calcularDigitoCpf(string $base): int
{
    $tamanho = strlen($base);
    $pesoInicial = $tamanho + 1;
    $soma = 0;

    for ($i = 0; $i < $tamanho; $i++) {
        $digito = (int) $base[$i];
        $peso = $pesoInicial - $i;
        $soma += $digito * $peso;
    }

    $resto = ($soma * 10) % 11;

    return $resto === 10 ? 0 : $resto;
}

function todosDigitosIguais(string $digitos): bool
{
    $tamanho = strlen($digitos);
    if ($tamanho === 0) {
        return false;
    }

    $primeiro = $digitos[0];
    for ($i = 1; $i < $tamanho; $i++) {
        if ($digitos[$i] !== $primeiro) {
            return false;
        }
    }

    return true;
}

function formatarCpf(string $digitos): string
{
    return substr($digitos, 0, 3)
        . '.' . substr($digitos, 3, 3)
        . '.' . substr($digitos, 6, 3)
        . '-' . substr($digitos, 9, 2);
}

function mascararCpf(string $digitos): string
{
    if (strlen($digitos) !== 11) {
        return '';
    }

    return '***.'. substr($digitos, 3, 3) . '.' . substr($digitos, 6, 3) . '-**';
}

function resultadoCpf(bool $valido, string $motivo, string $cpf, string $mensagem): array
{
    return [
        'valido' => $valido,
        'motivo' => $motivo,
        'cpf' => $cpf,
        'mensagem' => $mensagem,
    ];
}

function validarCpf(string $entrada): array
{
    $limpo = trim($entrada);

    if ($limpo === '') {
        return resultadoCpf(false, 'vazio', '', 'Informe o CPF.');
    }

    $apenasDigitos = '';
    if (preg_match('/^[0-9]{11}\z/D', $limpo) === 1) {
        $apenasDigitos = $limpo;
    } elseif (preg_match('/^[0-9]{3}\.[0-9]{3}\.[0-9]{3}-[0-9]{2}\z/D', $limpo) === 1) {
        $apenasDigitos = str_replace(['.', '-'], '', $limpo);
    } else {
        if (preg_match('/^[0-9]+\z/D', $limpo) === 1) {
            $quantidade = strlen($limpo);
            return resultadoCpf(
                false,
                'quantidade',
                '',
                "O CPF deve ter 11 dígitos; foram informados {$quantidade}."
            );
        }

        return resultadoCpf(
            false,
            'formato',
            '',
            'Formato inválido: use 11 dígitos ou 000.000.000-00.'
        );
    }

    if (todosDigitosIguais($apenasDigitos)) {
        return resultadoCpf(
            false,
            'repetidos',
            '',
            'CPF inválido: todos os dígitos são iguais.'
        );
    }

    $base9 = substr($apenasDigitos, 0, 9);
    $dv1Esperado = calcularDigitoCpf($base9);
    $dv1Informado = (int) $apenasDigitos[9];

    if ($dv1Esperado !== $dv1Informado) {
        return resultadoCpf(
            false,
            'digito',
            '',
            'CPF inválido: o 1º dígito verificador não confere.'
        );
    }

    $base10 = $base9 . (string) $dv1Esperado;
    $dv2Esperado = calcularDigitoCpf($base10);
    $dv2Informado = (int) $apenasDigitos[10];

    if ($dv2Esperado !== $dv2Informado) {
        return resultadoCpf(
            false,
            'digito',
            '',
            'CPF inválido: o 2º dígito verificador não confere.'
        );
    }

    return resultadoCpf(true, 'ok', $apenasDigitos, 'CPF válido.');
}

$casosTeste = [
    ['n' => 1,  'entrada' => '529.982.247-25',       'valido' => true,  'motivo' => 'ok',         'cpf' => '52998224725', 'msg_contem' => 'CPF válido.'],
    ['n' => 2,  'entrada' => '52998224725',          'valido' => true,  'motivo' => 'ok',         'cpf' => '52998224725', 'msg_contem' => 'CPF válido.'],
    ['n' => 3,  'entrada' => "  529.982.247-25  \t", 'valido' => true,  'motivo' => 'ok',         'cpf' => '52998224725', 'msg_contem' => 'CPF válido.'],
    ['n' => 4,  'entrada' => '111.444.777-35',       'valido' => true,  'motivo' => 'ok',         'cpf' => '11144477735', 'msg_contem' => 'CPF válido.'],
    ['n' => 5,  'entrada' => '123.456.789-09',       'valido' => true,  'motivo' => 'ok',         'cpf' => '12345678909', 'msg_contem' => 'CPF válido.'],
    ['n' => 6,  'entrada' => '529.982.247-24',       'valido' => false, 'motivo' => 'digito',     'cpf' => '',            'msg_contem' => '2º dígito verificador'],
    ['n' => 7,  'entrada' => '529.982.247-35',       'valido' => false, 'motivo' => 'digito',     'cpf' => '',            'msg_contem' => '1º dígito verificador'],
    ['n' => 8,  'entrada' => '111.111.111-11',       'valido' => false, 'motivo' => 'repetidos',  'cpf' => '',            'msg_contem' => 'todos os dígitos são iguais'],
    ['n' => 9,  'entrada' => '000.000.000-00',       'valido' => false, 'motivo' => 'repetidos',  'cpf' => '',            'msg_contem' => 'todos os dígitos são iguais'],
    ['n' => 10, 'entrada' => '5299822472',           'valido' => false, 'motivo' => 'quantidade', 'cpf' => '',            'msg_contem' => 'foram informados 10.'],
    ['n' => 11, 'entrada' => '529982247255',         'valido' => false, 'motivo' => 'quantidade', 'cpf' => '',            'msg_contem' => 'foram informados 12.'],
    ['n' => 12, 'entrada' => '529.982.247.25',       'valido' => false, 'motivo' => 'formato',    'cpf' => '',            'msg_contem' => 'Formato inválido'],
    ['n' => 13, 'entrada' => '529 982 247 25',       'valido' => false, 'motivo' => 'formato',    'cpf' => '',            'msg_contem' => 'Formato inválido'],
    ['n' => 14, 'entrada' => '52998224A25',          'valido' => false, 'motivo' => 'formato',    'cpf' => '',            'msg_contem' => 'Formato inválido'],
    ['n' => 15, 'entrada' => '',                     'valido' => false, 'motivo' => 'vazio',      'cpf' => '',            'msg_contem' => 'Informe o CPF.'],
    ['n' => 16, 'entrada' => "   \t  ",             'valido' => false, 'motivo' => 'vazio',      'cpf' => '',            'msg_contem' => 'Informe o CPF.'],
    ['n' => 17, 'entrada' => "529.982.247-2\xEF\xBC\x95", 'valido' => false, 'motivo' => 'formato', 'cpf' => '',         'msg_contem' => 'Formato inválido'],
    ['n' => 18, 'entrada' => '529-982-247.25',       'valido' => false, 'motivo' => 'formato',    'cpf' => '',            'msg_contem' => 'Formato inválido'],
    ['n' => 19, 'entrada' => "52998224725\n",        'valido' => true,  'motivo' => 'ok',         'cpf' => '52998224725', 'msg_contem' => 'CPF válido.'],
    ['n' => 20, 'entrada' => '529.982.24725',        'valido' => false, 'motivo' => 'formato',    'cpf' => '',            'msg_contem' => 'Formato inválido'],
    ['n' => 21, 'entrada' => '00000000000',          'valido' => false, 'motivo' => 'repetidos',  'cpf' => '',            'msg_contem' => 'todos os dígitos são iguais'],
    ['n' => 22, 'entrada' => '123.456.789-10',       'valido' => false, 'motivo' => 'digito',     'cpf' => '',            'msg_contem' => '1º dígito verificador'],
    ['n' => 23, 'entrada' => '987.654.321-00',       'valido' => true,  'motivo' => 'ok',         'cpf' => '98765432100', 'msg_contem' => 'CPF válido.'],
    ['n' => 24, 'entrada' => 'abc',                  'valido' => false, 'motivo' => 'formato',    'cpf' => '',            'msg_contem' => 'Formato inválido'],
];

$resultadosCasos = [];
$passaramCasos = 0;

foreach ($casosTeste as $caso) {
    $obtido = validarCpf($caso['entrada']);
    $ok = ($obtido['valido'] === $caso['valido'])
        && ($obtido['motivo'] === $caso['motivo'])
        && ($obtido['cpf'] === $caso['cpf'])
        && str_contains($obtido['mensagem'], $caso['msg_contem']);

    if ($ok) {
        $passaramCasos++;
    }

    $resultadosCasos[] = [
        'caso' => $caso,
        'obtido' => $obtido,
        'ok' => $ok,
    ];
}

$totalCasos = count($casosTeste);

$testesApoio = [
    [
        'descricao' => "calcularDigitoCpf('529982247')",
        'esperado' => '2',
        'obtido' => (string) calcularDigitoCpf('529982247'),
        'ok' => calcularDigitoCpf('529982247') === 2,
    ],
    [
        'descricao' => "calcularDigitoCpf('5299822472')",
        'esperado' => '5',
        'obtido' => (string) calcularDigitoCpf('5299822472'),
        'ok' => calcularDigitoCpf('5299822472') === 5,
    ],
    [
        'descricao' => "calcularDigitoCpf('123456789')",
        'esperado' => '0 (resto 10)',
        'obtido' => (string) calcularDigitoCpf('123456789'),
        'ok' => calcularDigitoCpf('123456789') === 0,
    ],
    [
        'descricao' => "calcularDigitoCpf('1234567890')",
        'esperado' => '9',
        'obtido' => (string) calcularDigitoCpf('1234567890'),
        'ok' => calcularDigitoCpf('1234567890') === 9,
    ],
    [
        'descricao' => "calcularDigitoCpf('111444777') · ('1114447773')",
        'esperado' => '3 · 5',
        'obtido' => calcularDigitoCpf('111444777') . ' · ' . calcularDigitoCpf('1114447773'),
        'ok' => calcularDigitoCpf('111444777') === 3 && calcularDigitoCpf('1114447773') === 5,
    ],
    [
        'descricao' => "calcularDigitoCpf('987654321') · ('9876543210')",
        'esperado' => '0 · 0',
        'obtido' => calcularDigitoCpf('987654321') . ' · ' . calcularDigitoCpf('9876543210'),
        'ok' => calcularDigitoCpf('987654321') === 0 && calcularDigitoCpf('9876543210') === 0,
    ],
    [
        'descricao' => "calcularDigitoCpf('000000000')",
        'esperado' => '0',
        'obtido' => (string) calcularDigitoCpf('000000000'),
        'ok' => calcularDigitoCpf('000000000') === 0,
    ],
    [
        'descricao' => "calcularDigitoCpf('111111111') · ('1111111111')",
        'esperado' => '1 · 1',
        'obtido' => calcularDigitoCpf('111111111') . ' · ' . calcularDigitoCpf('1111111111'),
        'ok' => calcularDigitoCpf('111111111') === 1 && calcularDigitoCpf('1111111111') === 1,
    ],
    [
        'descricao' => "todosDigitosIguais('11111111111') · ('11111111112') · ('00000000000')",
        'esperado' => 'sim · não · sim',
        'obtido' => (todosDigitosIguais('11111111111') ? 'sim' : 'não')
            . ' · ' . (todosDigitosIguais('11111111112') ? 'sim' : 'não')
            . ' · ' . (todosDigitosIguais('00000000000') ? 'sim' : 'não'),
        'ok' => todosDigitosIguais('11111111111') === true
            && todosDigitosIguais('11111111112') === false
            && todosDigitosIguais('00000000000') === true,
    ],
    [
        'descricao' => "formatarCpf('52998224725')",
        'esperado' => '529.982.247-25',
        'obtido' => formatarCpf('52998224725'),
        'ok' => formatarCpf('52998224725') === '529.982.247-25',
    ],
    [
        'descricao' => "mascararCpf('52998224725')",
        'esperado' => '***.982.247-**',
        'obtido' => mascararCpf('52998224725'),
        'ok' => mascararCpf('52998224725') === '***.982.247-**',
    ],
];

$passaramApoio = 0;
foreach ($testesApoio as $testeApoio) {
    if ($testeApoio['ok']) {
        $passaramApoio++;
    }
}
$totalApoio = count($testesApoio);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exercício 5 — Validador de CPF</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; line-height: 1.4; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 28px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; vertical-align: top; }
        th { background-color: #f4f4f4; }
        .ok { background-color: #e6f7ea; color: #136f2a; font-weight: bold; }
        .falhou { background-color: #fdeaea; color: #a61b1b; font-weight: bold; }
        .resumo { font-weight: bold; margin-bottom: 12px; }
    </style>
</head>
<body>
    <h1>Validador de CPF — Bateria de Testes</h1>
    <p class="resumo"><?= htmlspecialchars("{$passaramCasos} de {$totalCasos} testes passaram", ENT_QUOTES, 'UTF-8') ?></p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Entrada</th>
                <th>Esperado</th>
                <th>Obtido</th>
                <th>Mensagem</th>
                <th>CPF mascarado</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resultadosCasos as $item): ?>
                <?php
                $caso = $item['caso'];
                $obtido = $item['obtido'];
                $ok = $item['ok'];
                $esperadoTexto = ($caso['valido'] ? 'válido' : 'inválido') . ' / ' . $caso['motivo'];
                $obtidoTexto = ($obtido['valido'] ? 'válido' : 'inválido') . ' / ' . $obtido['motivo'];
                $mascarado = $obtido['cpf'] !== '' ? mascararCpf($obtido['cpf']) : '—';
                ?>
                <tr>
                    <td><?= (int) $caso['n'] ?></td>
                    <td>[<?= htmlspecialchars($caso['entrada'], ENT_QUOTES, 'UTF-8') ?>]</td>
                    <td><?= htmlspecialchars($esperadoTexto, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($obtidoTexto, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($obtido['mensagem'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($mascarado, ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="<?= $ok ? 'ok' : 'falhou' ?>"><?= $ok ? 'OK' : 'FALHOU' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Funções de Apoio</h2>
    <p class="resumo"><?= htmlspecialchars("{$passaramApoio} de {$totalApoio} testes de apoio passaram", ENT_QUOTES, 'UTF-8') ?></p>

    <table>
        <thead>
            <tr>
                <th>Chamada</th>
                <th>Esperado</th>
                <th>Obtido</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($testesApoio as $apoio): ?>
                <tr>
                    <td><?= htmlspecialchars($apoio['descricao'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($apoio['esperado'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($apoio['obtido'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="<?= $apoio['ok'] ? 'ok' : 'falhou' ?>"><?= $apoio['ok'] ? 'OK' : 'FALHOU' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>