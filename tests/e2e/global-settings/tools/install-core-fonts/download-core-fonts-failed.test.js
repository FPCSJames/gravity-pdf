import { RequestMock } from 'testcafe'
import { baseURL } from '../../../auth'
import InsallCoreFonts from '../../../page-model/global-settings/tools/install-core-fonts/install-core-fonts'

const run = new InsallCoreFonts()
const mock = RequestMock()
  .onRequestTo(`${baseURL}/wp-admin/admin-ajax.php`)
  .respond({}, 500, { 'access-Control-Allow-Origin': '*' })

fixture`Tools Tab - Download Core Fonts Test`
  .requestHooks(mock)

test('should return download core fonts error/failed', async t => {
  // Actions
  await run.navigateSettingsTab('gf_settings&subview=PDF&tab=tools#')

  // Assertions
  await t
    .expect(run.pendingResult.exists).ok()
    .expect(run.downloadFailed.exists).ok()
    .expect(run.retryDownload.exists).ok()
})
