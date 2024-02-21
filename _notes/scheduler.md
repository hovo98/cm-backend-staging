# Scheduler Commands

### Test Job
```
TestJob() - remove this. Does nothing.
```

### Finish 2nd Step
`App\Jobs\Lender\Deal\FinishSecondStep`
This appears to send out a notification to users that have no completed a second step of signup
which would set values for `meta`.
1. Gets Users with:
  a.  the role of *Lender*
  b.  that were recently created (within the last hour)
  c.  where `$user->notify` and `$user->meta` are both null.
  d.  also checks an `env` config for `app_check_env` which has a value of either `beta` or `site`

User->meta (example values):
```
{
  "perfect_fit": {
    "areas": [
      {
        "area": {
          "lat": 34.0489281,
          "city": "",
          "long": -111.0937311,
          "state": "Arizona",
          "county": "",
          "country": "United States",
          "place_id": "ChIJaxhMy-sIK4cRcc3Bf7EnOUI",
          "zip_code": "",
          "fips_code": "040070006002057",
          "long_name": "",
          "sublocality": "",
          "polygon_location": "polygon-locations/lat_34.0489281_long_-111.0937311.json",
          "formatted_address": "Arizona, USA"
        },
        "exclusions": [

        ]
      }
    ],
    "loan_size": {
      "max": 53000000,
      "min": 17000000
    },
    "asset_types": [
      1,
      4
    ],
    "multifamily": null,
    "type_of_loans": [

    ],
    "other_asset_types": [

    ]
  }
}
```


### ChooseQuoteBroker
`App\Jobs\Broker\Quote\ChooseQuoteBroker`


