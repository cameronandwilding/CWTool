<?php
/**
 * @file
 *
 * Issue content model.
 */

/**
 * Class EXAMPLE_CWToolContentTypeWrapper
 */
class EXAMPLE_CWToolContentTypeWrapper extends CWToolNodeWrapper {

  // Content type name.
  const TYPE_NAME = 'issue';

  // Approval workflow name.
  const WORKFLOW_APPROVAL = 'approval_workflow';

  // Approved workflow state name.
  const WORKFLOW_STATE_APPROVED = 'approved';

  /**
   * Check if issue is approved.
   *
   * @return bool
   */
  public function isApproved() {
    $data = $this->getDrupalObject();

    if (empty($data->workflow)) {
      return FALSE;
    }

    $issue_sid = $data->workflow;
    /* @var $approval_workflow Workflow */
    $approval_workflow = workflow_load_by_name(self::WORKFLOW_APPROVAL);

    if (empty($approval_workflow->wid)) {
      return FALSE;
    }

    /* @var $approved_state WorkflowState */
    $approved_state = workflow_state_load_by_name(self::WORKFLOW_STATE_APPROVED, $approval_workflow->wid);

    if (empty($approved_state->sid)) {
      return FALSE;
    }

    return $approved_state->sid == $issue_sid;
  }

  /**
   * Get the region code.
   *
   * @return string|NULL
   */
  public function getRegion() {
    return $this->field_issue_region->value();
  }

  /**
   * Get the region related DateTime.
   *
   * @param string $time
   *  String format for initialization.
   * @return DateTime
   * @throws Exception
   */
  public function getRegionRelatedDateTime($time = 'now') {
    $region_code = $this->getRegion();
    $timezone_name = darwin_scheduler_get_timezone_of_region($region_code);

    if (empty($timezone_name)) {
      throw new Exception(__METHOD__ . ' Missing timezone for issue: ' . $this->entityID);
    }

    $date = new DateTime($time);
    $date->setTimezone(new DateTimeZone($timezone_name));
    return $date;
  }

  /**
   * Get the Publishing time.
   *
   * @return DateTime
   * @throws Exception
   */
  public function getPublishingDateTime() {
    $publishing_day_date_items = field_get_items($this->entityType, $this->getDrupalObject(), 'field_issue_date');
    $publishing_day_date_item = current($publishing_day_date_items);
    $publishing_day_dataTime = new DateTime($publishing_day_date_item['value']);

    $date_time = self::getRegionRelatedDateTime();
    $date_time->setDate(
      $publishing_day_dataTime->format('Y'),
      $publishing_day_dataTime->format('m'),
      $publishing_day_dataTime->format('d')
    );
    $date_time->setTime((int) darwin_config_get_publish_hour(), 0);
    return $date_time;
  }

}
