import http from 'k6/http';
import { sleep,check } from 'k6';
import { randomIntBetween } from 'https://jslib.k6.io/k6-utils/1.2.0/index.js';

export const options = {
    // Key configurations for avg load test in this section
    stages: [
      { duration: '30s', target: 100 }, // traffic ramp-up from 1 to 100 users over 5 minutes.
      { duration: '30', target: 100 }, // stay at 100 users for 30 minutes
      { duration: '10s', target: 0 }, // ramp-down to 0 users
    ],
  };
  

export default function () {
  var randomValue = randomIntBetween(1, 2 );
  if (randomValue == 1 ) {
    const res = http.post('http://test.k6.io/'); http.get('http://127.0.0.1/api/order/')
    check(res, {
        'is status 200': (r) => r.status === 200,
      });
  }else {
    const res = http.post('http://127.0.0.1/api/order/')
    check(res, {
        'is status 200': (r) => r.status === 200,
      });
      const res2 =http.post('http://127.0.0.1/api/order/1/pay')
      check(res2, {
        'is status 200': (r) => r.status === 200,
      });
  }
  sleep(1);
}