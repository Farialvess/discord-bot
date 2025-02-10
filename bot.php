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
            'token' => $token,
        ]);

        // Quando o bot estiver pronto
        $this->discord->on('ready', function (Discord $discord) {
            echo "✅ O bot está online!\n";

            // Atualiza a presença do bot (status online e mensagem de atividade)
            $discord->updatePresence([
                'status' => 'online',
                'activity' => [
                    'name' => 'a dominar o mundo!',
                    'type' => 0 // 0 = Jogando, 1 = Transmitindo, 2 = Ouvindo, 3 = Assistindo
                ]
            ]);

            $this->registerEvents($discord);
        });
    }

    private function registerEvents(Discord $discord) {
        // Evento de mensagem recebida
        $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) {
            // Ignorar mensagens de outros bots
            if ($message->author->bot) {
                return;
            }
            $this->handleMessage($message);
        });
    }

    private function handleMessage(Message $message) {
        // Converte a mensagem para minúsculas e remove espaços extras
        $content = strtolower(trim($message->content));

        switch ($content) {
            case '!ping':
                $message->reply('🏓 Pong!');
                echo "📩 Comando '!ping' recebido!\n";
                break;

            case '!ola':
                $message->reply('👋 Olá, esperto!');
                echo "📩 Comando '!ola' recebido!\n";
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

// Obtém o token a partir das variáveis de ambiente
$token = getenv('DISCORD_TOKEN');

// Verifica se o token está definido
if (!$token) {
    echo "❌ ERRO: Token não definido! Configura a variável de ambiente DISCORD_TOKEN.\n";
    exit(1);
} else {
    echo "🔑 Token carregado com sucesso!\n";
}

// Cria a instância do bot e inicia
$bot = new MyDiscordBot($token);
$bot->run();
