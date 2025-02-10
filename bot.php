<?php
require __DIR__ . '/vendor/autoload.php';

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Discord\WebSockets\Intents;

class MyDiscordBot {
    private $discord;

    public function __construct($token) {
        echo "🔄 A iniciar o bot...\n";

        // Configura os intents necessários para captar mensagens (incluindo o conteúdo)
        $this->discord = new Discord([
            'token'   => $token,
            'intents' => Intents::GUILDS | Intents::GUILD_MESSAGES | Intents::MESSAGE_CONTENT,
        ]);

        // Quando o bot estiver pronto
        $this->discord->on('ready', function (Discord $discord) {
            echo "✅ O bot está online!\n";
            $this->registerEvents($discord);
        });
    }

    private function registerEvents(Discord $discord) {
        // Evento de mensagem recebida
        $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) {
            // Ignora mensagens de outros bots
            if ($message->author->bot) {
                return;
            }
            $this->handleMessage($message);
        });
    }

    private function handleMessage(Message $message) {
        // Converte a mensagem para minúsculas e remove espaços extras
        $content = strtolower(trim($message->content));
        echo "📩 Mensagem recebida: {$content}\n";

        switch ($content) {
            case '!ping':
                $message->reply('🏓 Pong!');
                echo "📩 Comando '!ping' processado!\n";
                break;
            case '!ola':
                $message->reply('👋 Olá, esperto!');
                echo "📩 Comando '!ola' processado!\n";
                break;
            case '!sorare':
                // Lista os jogadores do clube na API do Sorare usando o slug "farialves2007"
                $players = getSorareUserPlayers("farialves2007");
                if (is_array($players)) {
                    $reply = "Os teus jogadores no clube: " . implode(", ", $players);
                } else {
                    $reply = $players;
                }
                $message->reply($reply);
                echo "📩 Comando '!sorare' processado!\n";
                break;
            default:
                echo "📩 Mensagem ignorada: {$content}\n";
                break;
        }
    }

    public function run() {
        $this->discord->run();
    }
}

// Função que consulta a API GraphQL do Sorare e retorna os nomes dos jogadores
function getSorareUserPlayers($slug) {
    $url = "https://api.sorare.com/graphql";
    $query = <<<'GRAPHQL'
query GetUserCards($slug: String!) {
  user(slug: $slug) {
    cards {
      nodes {
        player {
          displayName
        }
      }
    }
  }
}
GRAPHQL;

    $variables = [
        "slug" => $slug
    ];

    $data = json_encode([
        "query" => $query,
        "variables" => $variables
    ]);

    $options = [
        "http" => [
            "header"  => "Content-Type: application/json",
            "method"  => "POST",
            "content" => $data
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === false) {
         return "Erro ao contactar a API do Sorare.";
    }
    $resultJson = json_decode($result, true);
    if (isset($resultJson['data']['user']['cards']['nodes'])) {
         $nodes = $resultJson['data']['user']['cards']['nodes'];
         $players = [];
         foreach ($nodes as $node) {
             if (isset($node['player']['displayName'])) {
                 $players[] = $node['player']['displayName'];
             }
         }
         if (empty($players)) {
             return "Não tens jogadores no clube.";
         }
         return $players;
    } else {
         return "Não foram encontrados jogadores.";
    }
}

// Obtém o token a partir das variáveis de ambiente
$token = getenv('DISCORD_TOKEN');

if (!$token) {
    echo "❌ ERRO: Token não definido! Configura a variável de ambiente DISCORD_TOKEN.\n";
    exit(1);
} else {
    echo "🔑 Token carregado com sucesso!\n";
}

// Cria a instância do bot e inicia
$bot = new MyDiscordBot($token);
$bot->run();
