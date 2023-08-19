import http from 'k6/http';
import { sleep,check,group } from 'k6';
import { randomIntBetween } from 'https://jslib.k6.io/k6-utils/1.2.0/index.js';

export const options = {
    // Key configurations for avg load test in this section
    stages: [
      { duration: '5s', target: 100 }, // traffic ramp-up from 1 to 100 users over 5 minutes.
      { duration: '10s', target: 150 }, // stay at 100 users for 30 minutes
      { duration: '5s', target: 5 }, // ramp-down to 0 users
    ],
  };
  

export default function () {

  group('購物車轉化率', function () {
      const res =  http.post('http://127.0.0.1/api/add_cart')
      check(res, {
          'add_cart is status 200': (r) => r.status === 200,
      });

      sleep(5);
      var randomValue = randomIntBetween(1, 2 );
      if (randomValue == 1 ) {
        const res2 =http.post('http://127.0.0.1/api/order/')
      check(res2, {
        'order is status 200': (r) => r.status === 200,
      });
      }
    
    sleep(1);
  });

  group('付款轉化率', function () {
    var randomValue = randomIntBetween(1, 3 );
    if (randomValue == 1 ) {
      const res2 =http.post('http://127.0.0.1/api/order/1/pay')
        check(res2, {
          'pay is status 200': (r) => r.status === 200,
        });
    }
    sleep(1);
  });
}