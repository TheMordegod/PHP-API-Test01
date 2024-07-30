const getData = async () => {
  const query = fetch("http://localhost:3000/api/users")
    .then((response) => {
      if (!response.ok) {
        throw new Error("Erro na requisição " + response.statusText);
      }
      return response.json();
    })
    .then((data) => {
      const queryList = document.getElementById("queryResult");
      data.forEach((item) => {
        const listItem = document.createElement("li");
        listItem.innerHTML = item.nome;
        queryList.appendChild(listItem);
      });
    })
    .catch((error) => {
      console.error("Erro ao buscar dados:", error);
    });
};

getData();
