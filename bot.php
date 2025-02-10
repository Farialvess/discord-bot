<?php
require __DIR__ . '/vendor/autoload.php';

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use GuzzleHttp\Client;

class MyDiscordBot {
    private $discord;
    private $sorareUserSlug = "farialves2007"; // Substitui pelo teu username do Sorare

    public function __construct($token) {
        echo "🔄 A iniciar o bot...\n";

        $this->discord = new Discord([
            'token'   => $token,
            'intents' => Discord::INTENTS_ALL,
        ]);

        $this->discord->on('ready', function (Discord $discord) {
            echo "✅ O bot está online!\n";
            $this->registerEvents($discord);
        });
    }

    private function registerEvents(Discord $discord) {
        $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) {
            if ($message->author->bot) {
                return;
            }
            
            $content = strtolower(trim($message->content));
            echo "📩 Mensagem recebida: {$content}\n";

            if ($content === '!sorare') {
                $this->handleSorareCommand($message);
            }
        });
    }

    private function handleSorareCommand(Message $message) {
        $message->reply("🔄 A procurar os jogadores do clube...");
        $players = $this->getSorarePlayers();

        if ($players === null) {
            $message->reply("❌ Erro ao contactar a API do Sorare.");
        } elseif (empty($players)) {
            $message->reply("⚽ Nenhum jogador encontrado no clube.");
        } else {
            $response = "⚽ Jogadores do clube:\n" . implode("\n", array_map(fn($p) => "- {$p}", $players));
            $message->reply($response);
        }
    }

    private function getSorarePlayers() {
        $client = new Client(['base_uri' => 'https://api.sorare.com/graphql']);

        $query = <<<GRAPHQL
        query getUserCards(\$slug: String!) {
            user(slug: \$slug) {
                anyCards {
                    player {
                        displayName
                    }
                }
            }
        }
        GRAPHQL;

        try {
            $response = $client->post('', [
                'json' => [
                    'query' => $query,
                    'variables' => ['slug' => $this->sorareUserSlug]
                ],
                'headers' => ['Content-Type' => 'application/json']
            ]);

            $data = json_decode($response->getBody(), true);
            
            if (!isset($data['data']['user']['anyCards'])) {
                return null;
            }

            $cards = $data['data']['user']['anyCards'];
            $players = array_map(fn($card) => $card['player']['displayName'] ?? 'Desconhecido', $cards);

            return $players;
        } catch (\Exception $e) {
            echo "❌ Erro na API do Sorare: " . $e->getMessage() . "\n";
            return null;
        }
    }

    public function run() {
        $this->discord->run();
    }
}

// Obtém o token do Discord das variáveis de ambiente
$token = getenv('DISCORD_TOKEN');

if (!$token) {
    echo "❌ ERRO: Token do Discord não definido! Configura a variável de ambiente DISCORD_TOKEN.\n";
    exit(1);
}

// Inicia o bot
$bot = new MyDiscordBot($token);
$bot->run();
