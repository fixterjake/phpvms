<?php

use App\Services\FareService;
use App\Support\Math;

class FinanceTest extends TestCase
{
    protected $ac_svc,
              $ICAO = 'B777',
              $fareSvc;

    public function setUp()
    {
        parent::setUp();
        $this->addData('base');
        $this->fareSvc = app(FareService::class);
    }

    public function testFlightFaresNoOverride()
    {
        $flight = factory(App\Models\Flight::class)->create();
        $fare = factory(App\Models\Fare::class)->create();

        $this->fareSvc->setForFlight($flight, $fare);
        $subfleet_fares = $this->fareSvc->getForFlight($flight);

        $this->assertCount(1, $subfleet_fares);
        $this->assertEquals($fare->price, $subfleet_fares->get(0)->price);
        $this->assertEquals($fare->capacity, $subfleet_fares->get(0)->capacity);

        #
        # set an override now
        #
        $this->fareSvc->setForFlight($flight, $fare, [
            'price' => 50, 'capacity' => 400
        ]);

        # look for them again
        $subfleet_fares = $this->fareSvc->getForFlight($flight);

        $this->assertCount(1, $subfleet_fares);
        $this->assertEquals(50, $subfleet_fares[0]->price);
        $this->assertEquals(400, $subfleet_fares[0]->capacity);

        # delete
        $this->fareSvc->delFareFromFlight($flight, $fare);
        $this->assertCount(0, $this->fareSvc->getForFlight($flight));
    }

    /**
     * Assign percentage values and make sure they're valid
     */
    public function testFlightFareOverrideAsPercent()
    {
        $flight = factory(App\Models\Flight::class)->create();
        $fare = factory(App\Models\Fare::class)->create();

        $percent_incr = '20%';
        $percent_decr = '-20%';
        $percent_200 = '200%';

        $new_price = Math::addPercent($fare->price, $percent_incr);
        $new_cost = Math::addPercent($fare->cost, $percent_decr);
        $new_capacity = Math::addPercent($fare->capacity, $percent_200);

        $this->fareSvc->setForFlight($flight, $fare, [
            'price' => $percent_incr,
            'cost' => $percent_decr,
            'capacity' => $percent_200,
        ]);

        $ac_fares = $this->fareSvc->getForFlight($flight);

        $this->assertCount(1, $ac_fares);
        $this->assertEquals($new_price, $ac_fares[0]->price);
        $this->assertEquals($new_cost, $ac_fares[0]->cost);
        $this->assertEquals($new_capacity, $ac_fares[0]->capacity);
    }

    public function testSubfleetFaresNoOverride()
    {
        $subfleet = factory(App\Models\Subfleet::class)->create();
        $fare = factory(App\Models\Fare::class)->create();

        $this->fareSvc->setForSubfleet($subfleet, $fare);
        $subfleet_fares = $this->fareSvc->getForSubfleet($subfleet);

        $this->assertCount(1, $subfleet_fares);
        $this->assertEquals($fare->price, $subfleet_fares->get(0)->price);
        $this->assertEquals($fare->capacity, $subfleet_fares->get(0)->capacity);

        #
        # set an override now
        #
        $this->fareSvc->setForSubfleet($subfleet, $fare, [
            'price' => 50, 'capacity' => 400
        ]);

        # look for them again
        $subfleet_fares = $this->fareSvc->getForSubfleet($subfleet);

        $this->assertCount(1, $subfleet_fares);
        $this->assertEquals(50, $subfleet_fares[0]->price);
        $this->assertEquals(400, $subfleet_fares[0]->capacity);

        # delete
        $this->fareSvc->delFareFromSubfleet($subfleet, $fare);
        $this->assertCount(0, $this->fareSvc->getForSubfleet($subfleet));
    }

    public function testSubfleetFaresOverride()
    {
        $subfleet = factory(App\Models\Subfleet::class)->create();
        $fare = factory(App\Models\Fare::class)->create();

        $this->fareSvc->setForSubfleet($subfleet, $fare, [
            'price' => 50, 'capacity' => 400
        ]);

        $ac_fares = $this->fareSvc->getForSubfleet($subfleet);

        $this->assertCount(1, $ac_fares);
        $this->assertEquals(50, $ac_fares[0]->price);
        $this->assertEquals(400, $ac_fares[0]->capacity);

        #
        # update the override to a different amount and make sure it updates
        #

        $this->fareSvc->setForSubfleet($subfleet, $fare, [
            'price' => 150, 'capacity' => 50
        ]);

        $ac_fares = $this->fareSvc->getForSubfleet($subfleet);

        $this->assertCount(1, $ac_fares);
        $this->assertEquals(150, $ac_fares[0]->price);
        $this->assertEquals(50, $ac_fares[0]->capacity);

        # delete
        $this->fareSvc->delFareFromSubfleet($subfleet, $fare);
        $this->assertCount(0, $this->fareSvc->getForSubfleet($subfleet));
    }

    /**
     * Assign percentage values and make sure they're valid
     */
    public function testSubfleetFareOverrideAsPercent()
    {
        $subfleet = factory(App\Models\Subfleet::class)->create();
        $fare = factory(App\Models\Fare::class)->create();

        $percent_incr = '20%';
        $percent_decr = '-20%';
        $percent_200 = '200%';

        $new_price = Math::addPercent($fare->price, $percent_incr);
        $new_cost = Math::addPercent($fare->cost, $percent_decr);
        $new_capacity = Math::addPercent($fare->capacity, $percent_200);

        $this->fareSvc->setForSubfleet($subfleet, $fare, [
            'price' => $percent_incr,
            'cost' => $percent_decr,
            'capacity' => $percent_200,
        ]);

        $ac_fares = $this->fareSvc->getForSubfleet($subfleet);

        $this->assertCount(1, $ac_fares);
        $this->assertEquals($new_price, $ac_fares[0]->price);
        $this->assertEquals($new_cost, $ac_fares[0]->cost);
        $this->assertEquals($new_capacity, $ac_fares[0]->capacity);
    }
}
