# Galastri Framework
Microframework PHP 7 simples para estudo e criação de pequenos sistemas WEB.

## Documentação
- Em breve

## O que é este projeto
Este microframework PHP 7 MVC orientado a objetos está sendo usado para estudo pessoal para compreensão mais aprofundada dos fundamentos do PHP e dos padrões de projeto. A ideia é fazer um framework fácil de compreender e que dê mais liberdade para o programador fazer as coisas do seu jeito ao mesmo tempo que o direciona, como um guia.

**Atenção!** Este projeto está em fase alpha de desenvolvimento. Isso significa que ele pode sofrer alterações e remoções de funcionalidades e comportamentos sem prévio aviso que comprometem sistemas ou sites que utilizem versões anteriores. Portanto, evite atualizar o framework caso o esteja utilizando em um projeto real.

## Principais funcionalidades
- Conexão com banco de dados facilitada;
- Consultas SQL com paginação;
- Controle de autenticação;
- Controle de permissões;
- Validação de dados;
- Controle de rotas de URLs;
- Possibilidade de criação de APIs;
- Envio de e-mails através do próprio microframework através da biblioteca [PHPMailer](https://github.com/PHPMailer/PHPMailer).

## Estágio atual
- Verificação de bugs;
- Criação de site com a documentação de uso.

### Implementações futuras
- Acesse a [lista de implementações](https://github.com/andregalastri/galastri-framework/issues/2).

## Alterações importantes
- **05/04/2020**: Incorporada a biblioteca [PHPMailer](https://github.com/PHPMailer/PHPMailer) versão 6.1.5 como classe Mailer para envio de e-mails;
- **05/11/2019**: Melhorado sistema de parâmetros para rotas. Agora é possível definir rótulos aos parâmetros, recuperar estes parâmetros na controller através destes rótulos e obrigar a URL a contê-los ou não, dependendo da configuração;
- **25/10/2019**: Aplicado padrão Singleton para a classe principal Galastri e componentes Authentication, Chain, Debug, Permission, Redirect e Route;
- **21/10/2019**: Envio inicial.

