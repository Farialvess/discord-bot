import asyncio
from gql import Client, gql
from gql.transport.aiohttp import AIOHTTPTransport

async def main():
    # Define o transport para a API do Sorare
    transport = AIOHTTPTransport(url="https://api.sorare.com/graphql")

    # Cria o cliente e busca o schema (opcional, mas ajuda)
    async with Client(transport=transport, fetch_schema_from_transport=True) as session:
        # Query para obter os cartões (jogadores) do utilizador
        query = gql(
            """
            query getUserCards($slug: String!) {
                user(slug: $slug) {
                    anyCards {
                        player {
                            displayName
                        }
                        slug
                        name
                        rarityTyped
                    }
                }
            }
            """
        )

        # Define a variável com o slug (neste caso, "farialves2007")
        variables = {"slug": "farialves2007"}

        # Executa a query
        result = await session.execute(query, variable_values=variables)

        # Processa o resultado
        if "user" in result and result["user"] is not None and "anyCards" in result["user"]:
            cards = result["user"]["anyCards"]
            if not cards:
                print("Nenhum jogador encontrado no clube.")
            else:
                print("Jogadores do clube:")
                for card in cards:
                    if "player" in card and card["player"] is not None:
                        print(f"- {card['player']['displayName']}")
                    else:
                        print("- Informação do jogador indisponível")
        else:
            print("Erro: Não foi possível obter os jogadores. Resposta da API:")
            print(result)

asyncio.run(main())
