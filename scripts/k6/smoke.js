import http from 'k6/http';
import { check, sleep } from 'k6';

const baseUrl = __ENV.BASE_URL || 'http://127.0.0.1:8000';

export const options = {
  vus: 5,
  duration: '30s',
  thresholds: {
    http_req_failed: ['rate<0.05'],
    http_req_duration: ['p(95)<2000'],
  },
};

export default function smoke() {
  const home = http.get(`${baseUrl}/`);
  check(home, {
    'home status 200': (response) => response.status === 200,
  });

  const health = http.get(`${baseUrl}/health`);
  check(health, {
    'health status 200': (response) => response.status === 200,
    'health json': (response) => {
      try {
        const body = response.json();
        return typeof body.status === 'string';
      } catch {
        return false;
      }
    },
  });

  const posts = http.get(`${baseUrl}/posts`);
  check(posts, {
    'posts status 200': (response) => response.status === 200,
  });

  sleep(1);
}
