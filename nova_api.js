// index.js

async function fetchAshby(operationName, variables, query) {
  const url = `https://jobs.ashbyhq.com/api/non-user-graphql?op=${operationName}`;
  
  const response = await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36',
      'apollographql-client-name': 'frontend_non_user'
    },
    body: JSON.stringify({ operationName, variables, query })
  });

  return response.json();
}

async function obterDetalhesVaga(empresaSlug, vagaId) {
  // Ajustada a query para aceitar o nome da empresa e apenas o descriptionHtml
  const query = `
    query ApiJobPosting($organizationHostedJobsPageName: String!, $jobPostingId: String!) {
      jobPosting(organizationHostedJobsPageName: $organizationHostedJobsPageName, jobPostingId: $jobPostingId) {
        descriptionHtml
      }
    }
  `;

  const res = await fetchAshby(
    'ApiJobPosting', 
    { organizationHostedJobsPageName: empresaSlug, jobPostingId: vagaId }, 
    query
  );

  return res.data?.jobPosting?.descriptionHtml || "Descrição não encontrada.";
}

async function listarVagasComDescricao(empresaSlug) {
  console.log(`🚀 Iniciando busca de vagas para: ${empresaSlug}...\n`);

  const listQuery = `
    query ApiJobBoardWithTeams($organizationHostedJobsPageName: String!) {
      jobBoard: jobBoardWithTeams(organizationHostedJobsPageName: $organizationHostedJobsPageName) {
        jobPostings {
          id
          title
          locationName
        }
      }
    }
  `;

  try {
    const listRes = await fetchAshby('ApiJobBoardWithTeams', { organizationHostedJobsPageName: empresaSlug }, listQuery);
    
    if (!listRes.data?.jobBoard) {
      console.log("Empresa não encontrada ou sem vagas.");
      return;
    }

    const vagas = listRes.data.jobBoard.jobPostings;

    for (const vaga of vagas) {
      console.log(`📦 Processando: ${vaga.title}...`);
      
      // Agora passamos o slug da empresa e o id da vaga
      const htmlDescricao = await obterDetalhesVaga(empresaSlug, vaga.id);
      
      const linkVaga = `https://jobs.ashbyhq.com/${empresaSlug}/${vaga.id}`;

      // Remove as tags HTML apenas para o print do console não ficar poluído
      const textoPuro = htmlDescricao.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ');

      console.log("--------------------------------------------------");
      console.log(`TITULO: ${vaga.title}`);
      console.log(`LOCAL:  ${vaga.locationName}`);
      console.log(`LINK:   ${linkVaga}`);
      console.log(`RESUMO DA DESCRIÇÃO:`);
      console.log(`${textoPuro.substring(0, 200)}...`);
      console.log("--------------------------------------------------\n");

      // Pausa de 1 segundo entre as vagas
      await new Promise(r => setTimeout(r, 1000));
    }

    console.log(`✅ Finalizado. ${vagas.length} vagas processadas.`);

  } catch (error) {
    console.error('Erro no processo:', error.message);
  }
}

// Execução
listarVagasComDescricao('enter-ai');