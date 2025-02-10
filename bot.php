<?php
require __DIR__ . '/vendor/autoload.php';

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;

class MyDiscordBot {
    private $discord;

    public function __construct($token) {
        echo "🔄 A iniciar o bot...\n";

        $this->discord = new Discord([
            'token'   => $token,
            'intents' => Discord::INTENTS_ALL,
        ]);

        $this->discord->on('ready', function (Discord $discord) {
            echo "✅ O bot está online!\n";

            // Atualiza presença
            $discord->updatePresence([
                'status'   => 'online',
                'activity' => ['name' => 'a dominar o mundo!', 'type' => 0]
            ]);

            // Regista os eventos
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

            switch ($content) {
                case '!ping':
                    $message->reply('🏓 Pong!');
                    break;

                case '!ola':
                    $message->reply('👋 Olá, esperto!');
                    break;

                case '!sorare':
                    $this->handleSorareCommand($message);
                    break;

                default:
                    echo "📩 Mensagem ignorada: {$content}\n";
                    break;
            }
        });
    }

    private function handleSorareCommand(Message $message) {
        echo "🔄 A buscar jogadores do Sorare...\n";

        // Executa o script sorare.php e captura a saída
        $output = shell_exec('php sorare.php 2>&1');

        if (!$output) {
            $message->reply("❌ Erro ao obter os jogadores do Sorare.");
            return;
        }

        // Divide a resposta para não ultrapassar o limite de caracteres do Discord
        $chunks = str_split($output, 1900);
        foreach ($chunks as $chunk) {
            $message->reply("📋 **Jogadores do Sorare:**\n```$chunk```");
        }

        echo "✅ Dados do Sorare enviados para o Discord!\n";
    }

    public function run() {
        $this->discord->run();
    }
}

// Obtém o token do ambiente
$token = getenv('DISCORD_TOKEN');

if (!$token) {
    echo "❌ ERRO: Token não definido!\n";
    exit(1);
}

// Cria e inicia o bot
$bot = new MyDiscordBot($token);
$bot->run();
