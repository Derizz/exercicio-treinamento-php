# Exercício de Treinamento — PHP (Dias 1 e 2)

Repositório com os exercícios práticos de treinamento em **PHP** (executados em ambiente local via Apache/XAMPP e versionados com **Git**), baseados nos conteúdos dos Dias 1 e 2 da apostila de PHP.

## Estrutura do Repositório

O projeto é organizado por exercício, cada um com seu script principal, documentação (`LEIAME.txt`) e arquivos auxiliares exigidos pelo enunciado:

- `ex01-orcamento/` — Página de orçamento de pedido com variáveis, constantes, cálculos monetários em centavos inteiros, formatação em reais e laboratório de números.
- `ex02-detetive/` — Diagnóstico e correção de 11 defeitos (`bugs.php` intacto, `corrigido.php`, `defeitos.txt` e `LEIAME.txt`) abrangendo sintaxe, avisos/erros fatais, lógica e segurança.
- `ex03-frete/` — Cálculo de frete por peso, região, valor do pedido e entrega expressa (`if`/`elseif`, `match`, tipos estritos, retorno antecipado, bateria de testes e simulador).
- `ex04-relatorio/` — Relatório de estoque e catálogo de produtos usando laços (`for`, `while`, `foreach`, `break`, `continue`), acumuladores, casos extremos e simulações de reposição e descontos.
- `ex05-cpf/` — Validação formal e formatação/mascaramento de CPF com normalização, verificação de dígitos repetidos, cálculo dos dígitos verificadores e bateria de testes.

## Como Executar Localmente

1. Coloque ou mantenha a pasta do projeto dentro do diretório público do servidor local (por exemplo, `C:\xampp\htdocs\exercicios` ou conforme configurado no Apache/XAMPP).
2. Inicie o **Apache** no painel do **XAMPP**.
3. Acesse os arquivos no navegador via `http://localhost/...` apontando para a pasta do respectivo exercício:
   - `ex01-orcamento/orcamento.php`
   - `ex02-detetive/corrigido.php`
   - `ex03-frete/frete.php`
   - `ex04-relatorio/relatorio.php`
   - `ex05-cpf/cpf.php`

## Diretrizes e Boas Práticas Aplicadas

- Uso de `declare(strict_types=1);` nos arquivos aplicáveis e exibição de erros habilitada em ambiente de desenvolvimento.
- Separação entre lógica/retorno de funções e saída/exibição HTML, sem `echo` dentro de funções.
- Escape de saída com `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` para dados exibidos no HTML.
- Comparações estritas (`===` e `!==`) e testes de robustez com diferentes conjuntos de dados e casos de borda.
- Histórico de commits incremental no Git documentando as alterações de cada etapa.

## Observação sobre o Repositório

- Repositório no GitHub: `https://github.com/Derizz/exercicio-treinamento-php`.
- Cada pasta contém um arquivo `LEIAME.txt` com decisões técnicas, como abrir a página em `localhost`, respostas às perguntas do exercício e observações de teste.
