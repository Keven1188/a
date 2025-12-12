/**
 * Configuração da API
 * Detecta automaticamente a URL correta baseado no ambiente
 */

export const getApiUrl = (): string => {
  // Em desenvolvimento local
  if (typeof window !== 'undefined' && window.location.hostname === 'localhost') {
    return 'http://localhost:8080';
  }

  // Em produção, usar a mesma origem do frontend
  if (typeof window !== 'undefined') {
    return window.location.origin;
  }

  return 'http://localhost:8080';
};

const API_BASE_URL = getApiUrl();
console.log('API Base URL:', API_BASE_URL);

export const API_ENDPOINTS = {
  // tRPC endpoints
  TRPC: `${API_BASE_URL}/api/trpc`,
  
  // Legacy endpoints (para compatibilidade)
  LOGIN: `${API_BASE_URL}/api/users/login`,
  REGISTER: `${API_BASE_URL}/api/users`,
  PRODUCTS: `${API_BASE_URL}/api/products`,
  PRODUCT_BY_ID: (id: number) => `${API_BASE_URL}/api/products/${id}`,
  SEARCH_PRODUCTS: `${API_BASE_URL}/api/products/search`,
  CATEGORIES: `${API_BASE_URL}/api/products/categories`,
  PLATFORMS: `${API_BASE_URL}/api/products/platforms`,
  ORDERS: `${API_BASE_URL}/api/orders`,
  ORDER_BY_ID: (id: number) => `${API_BASE_URL}/api/orders/${id}`,
  USERS: `${API_BASE_URL}/api/users`,
  USER_BY_ID: (id: number) => `${API_BASE_URL}/api/users/${id}`,
};

/**
 * Fazer requisição para a API
 */
export async function apiCall<T>(
  endpoint: string,
  options: RequestInit = {}
): Promise<T> {
  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
  };
  
  if (options.headers && typeof options.headers === 'object') {
    Object.assign(headers, options.headers);
  }

  // Adicionar token de autenticação se existir
  const token = localStorage.getItem('auth_token');
  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  const response = await fetch(endpoint, {
    ...options,
    headers,
  });

  if (!response.ok) {
    throw new Error(`API Error: ${response.status} ${response.statusText}`);
  }

  return response.json();
}
