<?php
session_start();

// Função para validar o token do login
function validarToken($token) {
    $arquivoToken = 'tokem.txt';

    if (!file_exists($arquivoToken)) {
        die("Arquivo de token não encontrado.");
    }

    $tokenCorreto = trim(file_get_contents($arquivoToken));
    return $token === $tokenCorreto;
}

// Verificar se o usuário está logado
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    // Tela de login
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'])) {
        $token = trim($_POST['token']);
        if (validarToken($token)) {
            $_SESSION['logado'] = true;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $erro = "Token inválido. Tente novamente.";
        }
    }

    // Exibir tela de login
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f0f0f0;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
            }
            .login-container {
                background-color: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                width: 300px;
            }
            .login-container h1 {
                font-size: 24px;
                margin-bottom: 20px;
                text-align: center;
            }
            .login-container input[type="text"],
            .login-container button {
                width: 100%;
                padding: 10px;
                margin-bottom: 15px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 16px;
            }
            .login-container button {
                background-color: #007BFF;
                color: white;
                border: none;
                cursor: pointer;
                transition: background-color 0.3s;
            }
            .login-container button:hover {
                background-color: #0056b3;
            }
            .error {
                color: red;
                font-size: 14px;
                margin-bottom: 15px;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <h1>Login</h1>
            <?php if (isset($erro)): ?>
                <p class="error"><?php echo htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <form method="post">
                <input type="text" name="token" placeholder="Digite o token" required>
                <button type="submit">Entrar</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}
// Função para conectar ao banco de dados
function conectarBanco() {
    $servername = "localhost";
    $username = "u620302324_root";
    $password = "Ab741852963$";
    $dbname = "u620302324_painel";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Erro na conexão: " . $conn->connect_error);
    }
    return $conn;
}


// Função para gerar horário aleatório
function gerarHorarioAleatorio() {
    date_default_timezone_set('America/Sao_Paulo'); // Define o fuso horário de Brasília
    $hora = rand(8, 17);  // Gera uma hora entre 8h e 17h
    $minuto = rand(0, 59); // Gera um minuto aleatório
    $segundo = rand(0, 59); // Gera um segundo aleatório
    return sprintf("%02d:%02d:%02d", $hora, $minuto, $segundo); // Formata como HH:MM:SS
}


// Função para gerar uma data com intervalo fixo de 2 dias
function gerarProximaData($data_anterior, $dias_de_espera) {
    date_default_timezone_set('America/Sao_Paulo'); // Define o fuso horário de Brasília
    return date("Y-m-d", strtotime("$data_anterior +$dias_de_espera days"));
}

function gerarCodigoRastreamento($origem, $destino, $tipoCodigo, $quantidade) {
    $conn = conectarBanco();
    $resultados = [];

    if (empty($origem)) {
        $origem = "Não especificado";
    }

    if (!empty($destino)) {
        $destinos = explode("\n", $destino);
        foreach ($destinos as $linha) {
            $destinoCompleto = trim($linha); // Aceitar o destino exatamente como fornecido

            for ($i = 0; $i < $quantidade; $i++) {
                // Geração do código de rastreamento
                $codigo = gerarCodigoUnico($tipoCodigo);

                // Definir a data de criação com horário fixo para o primeiro código
                $data_criacao = date("Y-m-d") . ' 09:31'; // Data + horário fixo de 09:31
                $sql = "INSERT INTO rastreio (codigo, data_criacao, origem, destino) VALUES ('$codigo', '$data_criacao', '$origem', '$destinoCompleto')";

                if ($conn->query($sql) === TRUE) {
                    $resultados[] = "$codigo : $destinoCompleto";

                    // Definir as etapas conforme o tipo de código
                    $etapas = [];
                    if ($tipoCodigo === 'BR') {
                        $etapas = etapasBR();
                    } elseif ($tipoCodigo === 'INT') {
                        $etapas = etapasINT();
                    } else {
                        $etapas = etapasCN();
                    }

                    // Inserção das etapas no banco de dados com intervalos
                    $data_atual = date("Y-m-d");  // Data inicial (primeira etapa)
                    $primeiraEtapa = true; // Flag para garantir que a data de criação só seja inserida na primeira etapa

                    foreach ($etapas as $etapa) {
                        $localizacao = $etapa[0];
                        $descricao = $etapa[1];
                        $dias_espera = $etapa[2]; // Definido pela etapa, de acordo com o tipo (ex: 2, 3, 1...)
                        $notificacao = "Notificação para etapa: $descricao";

                        if ($primeiraEtapa) {
                            // Para a primeira etapa, inserir o horário fixo de 09:31
                            $data_hora_etapa = $data_atual . ' 09:31'; // Horário fixo de 09:31
                            $primeiraEtapa = false; // Depois da primeira etapa, não incluir mais o horário fixo
                        } else {
                            // Para as etapas subsequentes, gerar horário normal
                            $data_hora_etapa = $data_atual . ' ' . gerarHorarioAleatorio();
                        }

                        // Inserir a etapa no banco de dados com intervalo
                        $sql_etapa = "INSERT INTO rastreio_etapas (codigo, localizacao, data_hora, descricao, notificacao) 
                                      VALUES ('$codigo', '$localizacao', '$data_hora_etapa', '$descricao', '$notificacao')";

                        if ($conn->query($sql_etapa) !== TRUE) {
                            echo "<p>Erro ao inserir a etapa '$descricao': " . $conn->error . "</p>";
                        }

                        // Atualizar a data para a próxima etapa com o intervalo de dias
                        $data_atual = gerarProximaData($data_atual, $dias_espera);
                    }
                } else {
                    echo "<p>Erro ao inserir código de rastreamento: " . $conn->error . "</p>";
                }
            }
        }
    } else {
        // Caso o destino esteja vazio, gera o código sem destino
        $destinoCompleto = "Não especificado";
        for ($i = 0; $i < $quantidade; $i++) {
            $codigo = gerarCodigoUnico($tipoCodigo);
            $data_criacao = date("Y-m-d") . ' 09:31';  // Data de criação + horário fixo de 09:31
            $sql = "INSERT INTO rastreio (codigo, data_criacao, origem, destino) VALUES ('$codigo', '$data_criacao', '$origem', '$destinoCompleto')";
            
            if ($conn->query($sql) === TRUE) {
                $resultados[] = " $codigo : $origem";

                // Definir as etapas conforme o tipo de código
                $etapas = [];
                if ($tipoCodigo === 'BR') {
                    $etapas = etapasBR();
                } elseif ($tipoCodigo === 'INT') {
                    $etapas = etapasINT();
                } else {
                    $etapas = etapasCN();
                }

                // Inserção das etapas no banco de dados com intervalos
                $data_atual = date("Y-m-d");
                $primeiraEtapa = true; // Flag para garantir que a data de criação só seja inserida na primeira etapa

                foreach ($etapas as $etapa) {
                    $localizacao = $etapa[0];
                    $descricao = $etapa[1];
                    $dias_espera = $etapa[2]; // Número de dias entre etapas
                    $notificacao = "Notificação para etapa: $descricao";

                    if ($primeiraEtapa) {
                        // Para a primeira etapa, inserir o horário fixo de 09:31
                        $data_hora_etapa = $data_atual . ' 09:31'; // Horário fixo de 09:31
                        $primeiraEtapa = false; // Depois da primeira etapa, não incluir mais o horário fixo
                    } else {
                        // Para as etapas subsequentes, gerar horário normal
                        $data_hora_etapa = $data_atual . ' ' . gerarHorarioAleatorio();
                    }

                    $sql_etapa = "INSERT INTO rastreio_etapas (codigo, localizacao, data_hora, descricao, notificacao) 
                                  VALUES ('$codigo', '$localizacao', '$data_hora_etapa', '$descricao', '$notificacao')";

                    if ($conn->query($sql_etapa) !== TRUE) {
                        echo "<p>Erro ao inserir a etapa '$descricao': " . $conn->error . "</p>";
                    }

                    // Atualizar a data para a próxima etapa com o intervalo de dias
                    $data_atual = gerarProximaData($data_atual, $dias_espera);
                }
            } else {
                echo "<p>Erro ao inserir código de rastreamento: " . $conn->error . "</p>";
            }
        }
    }

    $conn->close();
    return $resultados;
}




// Função para gerar código único
function gerarCodigoUnico($tipoCodigo) {
    // Alterando para gerar códigos de acordo com o tipo selecionado
    if ($tipoCodigo === 'INT') {
        return 'CN' . rand(1000000000, 9999999999) . 'INT';
    } elseif ($tipoCodigo === 'CN') {
        return 'CN' . rand(1000000000, 9999999999) . 'CN';  // Código para China
    } else {
        return 'BR' . rand(1000000000, 9999999999) . 'BR';  // Código para Brasil
    }
}
// Definindo as etapas para o tipo BR
function etapasBR()  {
   return [
       
    ["CRIADO", "A entrega foi criada com sucesso. Aguarde os demais status de rastreamento.", 1],
    ["DESPACHADO", "O produto foi despachado na unidade de centro de distribuição.", 1],
    ["EM TRANSFERÊNCIA", "A carga está em processo de transferência entre filiais.", 2],
    ["ENTRADA FILIAL DESTINO", "A carga chegou na filial.", 3],
    ["PRODUTO EM ANÁLISE", "O seu produto está em processo de análise de liberação da alfândega.", 1],
    ["PRODUTO LIBERADO", "Seu produto foi analisado com sucesso, em breve estará disponível para entrega.", 3],
    ["PRODUTO EM ROTA DE ENTREGA", "Seu produto está em transferência para a cidade de destino.", 2],
    ["PRODUTO CHEGOU NA CIDADE DE DESTINO", "Objeto postado foi postado após o horário limite da unidade, sujeito encaminhamento no próximo dia útil.", 3],
    ["PRODUTO NA FILA DE ENTREGA", "Seu produto foi encaminhado para a fila de entrega, por conta da alta demanda de entregas feitas pela transportadora.", 2],
    ["PRODUTO SAIU PARA ENTREGA", "Seu produto saiu para entrega ao destinatário.", 2],
    ["FALHA NA ENTREGA", "O entregador não localizou o endereço do destinatário, será feito uma nova tentativa no dia seguinte.", 1],
    ["PRODUTO SAIU PARA ENTREGA", "Seu produto saiu para entrega ao destinatário.", 1],
    ["FALHA NA ENTREGA", "O entregador localizou o endereço e ao tentar entrar em contato não foi atendido, será feito uma nova tentativa no dia seguinte.", 1],
    ["PRODUTO SAIU PARA ENTREGA", "Seu produto saiu para entrega ao destinatário.", 1],
    ["PRODUTO ENTREGUE AO DESTINATÁRIO", "Seu produto foi entregue com sucesso para o destinatário.", 1]


    ];
}

// Definindo as etapas para o tipo CN
function etapasCN() {
    return [
        
    ["[Shunyu Logistics] - Recebido pela transportadora", "Notificação da transportadora: Sucesso de desembaraço de exportação", 1],
    ["[Dalingshan Town] - Em processamento no centro de triagem", "Notificação da transportadora: Entrada no centro de triagem", 3],
    ["[Dalingshan Town] - Sucesso no centro de triagem", "Notificação da transportadora: Saída no centro de triagem", 3],
    ["Notificação da transportadora", "Entrada no centro de triagem", 1],
    ["[Dalingshan Town]", "Sucesso no centro de triagem", 1],
    ["Recebido no centro de transporte de partida", "Notificação da transportadora: Chegou ao centro de transporte de partida", 3],
    ["Iniciado o desembaraço aduaneiro", "Notificação da transportadora: Despacho aduaneiro de exportação iniciado", 4],
    ["Fiscalização aduaneira finalizada", "Notificação da transportadora: Sucesso de desembaraço de exportação", 3],
    ["Chegou ao destino de transferência", "Notificação da transportadora: Chegou ao escritório da linehaul", 3],
    ["Saindo do país/região de origem", "Notificação da transportadora: Saindo do país/região de partida", 3],
    ["[BRASIL] - chegou às instalações locais.", "Notificação da transportadora: Recebido pela empresa de entrega local no país", 5],
    ["[CURITIBA, PR] - Unidade de logística integrada, par 16, fiscalização iniciada.", "Notificação da transportadora: Fiscalização iniciada.", 6],
    ["[CURITIBA, PR] - Unidade de logística integrada, par 16, fiscalização em processamento.", "Notificação da transportadora: Fiscalização em processamento.", 3],
    ["[CURITIBA, PR] - Unidade de logística integrada, par 16, fiscalização em processamento.", "Notificação da transportadora: Fiscalização em processamento.", 3],
    ["[CURITIBA, PR] - Unidade de logística integrada, par 16, fiscalização finalizada.", "Notificação da transportadora: Fiscalização finalizada.", 6],
    ["[CURITIBA, PR] - Unidade de logística integrada, RO 01, objeto em trânsito - por favor, aguarde.", "Notificação da transportadora: Objeto em trânsito.", 6],
    ["Unidade de logística, objeto em separação para envio - por favor, aguarde.", "Notificação da transportadora: Objeto em separação.", 3],
    ["Unidade de logística, ro 10, objeto em trânsito - por favor, aguarde.", "Notificação da transportadora: Objeto em trânsito.", 3],
    ["Objeto chegou à cidade/região de destino.", "Notificação da transportadora: Objeto chegou na região de destino.", 3],
    ["Objeto preparado para saída.", "Notificação da transportadora: Objeto em trânsito.", 6],
    ["PRODUTO ENTREGUE AO DESTINATÁRIO", "Seu produto foi entregue com sucesso para o destinatário", 3]


    ];
}

// Definindo as etapas para o tipo INT
function etapasINT() {
    return [
      
    ["Received by logistics company", "Carrier note: Accepted by carrier", 1],
    ["[Dalingshan Town] - Received by logistics company", "Carrier note: Accepted by carrier", 1],
    ["[Dalingshan Town] - Departed from sorting center", "Carrier note: Outbound in sorting center", 1],
    ["Arrived at departure transport hub", "Carrier note: Arrived at departure transport hub", 2],
    ["Export customs clearance complete", "Carrier note: Export clearance success", 1],
    ["Leaving from departure country/region", "Carrier note: Leaving from departure country/region", 3],
    ["Departed from departure country/region", "Carrier note: Left from departure country/region", 2],
    ["Arrived at linehaul office", "Carrier note: Arrived at linehaul office", 1],
    ["Arrived in transit country/region", "Carrier note: Arrive at transit country or district.", 1],
    ["Handed over from linehaul office", "Carrier note: At destination country/region", 2],
    ["Leaving transit country/region", "Carrier note: Parcel outbound from transit facility", 2],
    ["Departed from transit country/region", "Carrier note: Depart from transit country or district", 2],
    ["Awaiting for transit to final delivery office", "Carrier note: Package arrived at facility of the local delivery company", 2],
    ["Import customs clearance started", "Carrier note: Import clearance start", 1],
    ["Received by local delivery company", "Carrier note: Arrived at the import exchange office", 2],
    ["Import customs clearance complete", "Carrier note: Import customs clearance complete", 1],
    ["Arrived at destination country/region sorting center", "Carrier note: Factory sorting", 3],
    ["Departed from destination country/region sorting center", "Carrier note: Departure from the sorting center", 2],
    ["Departed from destination country/region sorting center", "Carrier note: Entered the PIC network", 1],
    ["Departed from destination country/region sorting center", "Carrier note: Departure from the sorting center", 2],
    ["Departed from destination country/region sorting center", "Carrier note: Departure from the sorting center", 1],
    ["Departed from destination country/region sorting center", "Carrier note: Departure from the sorting center", 3],
    ["Departed from destination country/region sorting center", "Carrier note: Departure from the sorting center", 2],
    ["Arrived at local delivery center", "Carrier note: Arrived", 1],
    ["Out for delivery", "Carrier note: Assigned to delivery note", 1]


    ];
}

// Verifica se os dados do formulário foram enviados
if (isset($_POST['gerar_codigo']) && isset($_POST['origem']) && isset($_POST['tipo_codigo']) && isset($_POST['quantidade'])) {
    $origem = $_POST['origem'];
    $destino = isset($_POST['destino']) ? $_POST['destino'] : '';  // Destino pode ser vazio
    $tipoCodigo = $_POST['tipo_codigo'];
    $quantidade = $_POST['quantidade']; // Captura a quantidade de códigos a serem gerados
    $resultados = gerarCodigoRastreamento($origem, $destino, $tipoCodigo, $quantidade);
}
?>



<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerar Código de Rastreamento</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        h1, h2 {
            color: #333;
        }
        form {
            background-color: #ffffff;
            padding: 20px;
            margin: 30px auto;
            width: 80%;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        label {
            font-size: 14px;
            color: #555;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        button {
            background-color: #007BFF;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #0056b3;
        }
        .resultados {
            background-color: #e9f7ef;
            padding: 15px;
            border: 1px solid #d4edda;
            border-radius: 4px;
            margin-top: 20px;
            font-size: 16px;
            color: #155724;
        }
    </style>
</head>
<body>
    <h1>Gerar Código de Rastreamento</h1>
    <form method="post">
        <label for="origem">Origem (opcional):</label>
        <input type="text" id="origem" name="origem" placeholder="Digite a origem" />

        <label for="destino">Destino (opcional):</label>
        <textarea id="destino" name="destino" rows="5" placeholder="Digite os destinos separados por linha (CEP, Bairro, Cidade, Estado)."></textarea>

        <label for="tipo_codigo">Tipo de Código:</label>
        <select id="tipo_codigo" name="tipo_codigo">
            <option value="BR">BR (Brasil)</option>
            <option value="CN">CN (China)</option>
            <option value="INT">INT (Internacional)</option>
        </select>

        <label for="quantidade">Quantidade de Códigos (sem origem/destino):</label>
        <input type="number" id="quantidade" name="quantidade" placeholder="Digite a quantidade de códigos a gerar" />

        <button type="submit" name="gerar_codigo">Gerar Código</button>
    </form>

  <?php if (!empty($resultados)): ?>
    <div class="resultados">
        <h2>Resultados:</h2>
        <ul>
            <?php foreach ($resultados as $resultado): ?>
                <li><?php echo htmlspecialchars($resultado, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
        </ul>

        <!-- Área de texto para exibir todos os códigos -->
        <textarea id="codigoRastreamento" rows="5" cols="50">
            <?php echo implode("\n", $resultados); ?>
        </textarea>
        
        <!-- Botão para copiar todos os códigos -->
        <button class="button" onclick="copiarCodigos()">Copiar Todos os Códigos</button>
        
        <script>
        function copiarCodigos() {
            var codigoTexto = document.getElementById("codigoRastreamento");
            codigoTexto.select();  // Seleciona o conteúdo do textarea
            document.execCommand("copy");  // Copia o conteúdo para a área de transferência
            alert("Códigos copiados para a área de transferência!");
        }
        </script>
    </div>
    <?php endif; ?>
</body>
</html>