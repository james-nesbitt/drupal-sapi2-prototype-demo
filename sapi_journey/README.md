# SAPI User Journey
SAPI Journey is extension of SAPI module and provides tracking throughout users time spent on the site.

#### Behaviour of module:

- Store in sessions information about current "journey" and provide capability to track same journey on user login.

 - Capture each "step" on "journey" by creating data entry for each step and provide step number.

- Capture current user and responses URI and save it within each step.

#### Dependencies

- Statistics API (sapi)
- Statistics API Data entities (sapi_data)

##### To enable users journey tracking:
1. Enable sapi_user_journey;
2. Go to **/admin/config/sapi/statistics-plugins**
3. Enable ***user_journey*** and ***user_journey_tracker*** plugins
