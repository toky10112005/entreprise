const API_URL = 'http://localhost:8081/api'; // Remplacez par l'URL de votre CI4

export const authService = {
  async login(email, motDePasse) {
    const response = await fetch(`${API_URL}/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ email, mot_de_passe: motDePasse })
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.error || 'Erreur de connexion');
    }

    return data;
  }
};