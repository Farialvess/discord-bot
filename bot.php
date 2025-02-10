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
        $this->discord = new Discord([
            'token'   => $token,
            'intents' => Intents::GUILDS | Intents::GUILD_MESSAGES | Intents::MESSAGE_CONTENT,
        ]);
        $this->discord->on('ready', function (Discord $discord) {
            echo "✅ O bot está online!\n";
            $this->registerEvents($discord);
        });
    }

    private function registerEvents(Discord $discord) {
        $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) {
            // Ignora mensagens de outros bots
            if ($message->author->bot) {
                return;
            }
            $this->handleMessage($message);
        });
    }

    private function handleMessage(Message $message) {
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
                // Chama a função que consulta a API do Sorare usando o email
                $name = getSorareUserName("farialves2007@gmail.com");
                $message->reply("O teu nome é: " . $name);
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

// Função que consulta a API do Sorare e tenta obter o nome do utilizador
function getSorareUserName($email) {
    $url = "https://api.sorare.com/api/v1/users/" . urlencode($email);
    $response = file_get_contents($url);
    if ($response === false) {
         return "Não foi possível obter o nome (erro na requisição)";
    }
    $data = json_decode($response, true);
    if (isset($data['name'])) {
         return $data['name'];
    }
    // Se não houver 'name', extrai a parte antes do "@" do email
    return strstr($email, '@', true);
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
