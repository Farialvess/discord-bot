<?php
require __DIR__ . '/vendor/autoload.php';

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;

class MyDiscordBot {
    private $discord;

    public function __construct($token) {
        $this->discord = new Discord([
            'token' => $token,
        ]);

        // Quando o bot estiver pronto, regista os eventos
        $this->discord->on('ready', function (Discord $discord) {
            echo "O bot está online!\n";
            $this->registerEvents($discord);
        });
    }

    private function registerEvents(Discord $discord) {
        // Usa a constante do evento para quando uma mensagem for criada
        $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) {
            // Ignora mensagens de outros bots
            if ($message->author->bot) {
                return;
            }
            $this->handleMessage($message);
        });
    }

    private function handleMessage(Message $message) {
        // Converte a mensagem para minúsculas e retira espaços
        $content = strtolower(trim($message->content));
        switch ($content) {
            case '!ping':
                $message->reply('Pong!');
                break;
            case '!ola':
                $message->reply('Olá, esperto!');
                break;
            default:
                // Se quiseres adicionar mais comandos, mete-os aqui
                break;
        }
    }

    public function run() {
        $this->discord->run();
    }
}

// Obtém o token a partir das variáveis de ambiente
$token = getenv('DISCORD_TOKEN');
if (!$token) {
    echo "Token não definido!\n";
    exit(1);
}

// Cria a instância do bot e inicia
$bot = new MyDiscordBot($token);
$bot->run();
