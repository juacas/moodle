GDPR Plus : enhanced tool policies
--

[![Build Status](https://travis-ci.org/call-learning/moodle-tool_gdpr_plus.svg?branch=master)](https://travis-ci.org/call-learning/moodle-tool_gdpr_plus)

The aim of this plugin is to enhance the tool_policy plugin on the specific issue of user acceptation of the policy through the "cookie" banner.

The original tool_policy banner, only allow to accept, but we cannot revoke authorisation to use some cookies and there is no way to display the banner back once accepted.
We also tried to work on the fact that cookie acceptance is not linked to any specific policy, so with the current tool_policy we just accept cookies but do not link
this acceptance with the real policy.

Recent changes in GDPR/RGPD has made the acceptance or refusal mandatory. Refusal means refusing non-essential cookies (session cookies for example).
For reference, this is the new framework: https://www.cnil.fr/en/refusing-cookies-should-be-easy-accepting-them-results-second-campaign-orders-and-future-actions
(in French: https://www.cnil.fr/fr/nouvelles-regles-cookies-et-autres-traceurs-bilan-accompagnement-cnil-actions-a-venir)


Usage
--

Once the plugin has been installed, you need to set it as the main policy handler in Site administration > Users > Privacy and policies > Policy settings

Once this is done, the banner should appear automatically at the bottom of the page.

It will still use the policies defined in the tool_policy parameters and depending on a policy setting (authenticated user or not, mandatory policy or not). 
But it will display the right policies in the list once you click on "Show settings".

An additional link will be automatically added at the bottom of the page ("Show policies") to make the banner appear once again when the policies are accepted.

Note that the way we designed it was to rely on policies acceptance and not "cookie" acceptance: it means that if you want users to accept a given cookie 
(for example Google Analytics) you need to define the relevant policy to be accepted.

Once a policy has been accepted, it will send javascript "grpd_policies_accepted" message with the list of policies (policy version id) and acceptance status.
It is then up to the theme developer to enable the related javascript (for example Google Analytics) depending on which policy has been accepted.

Features
--

* Additional link to go back to settings
* Messaging through javascript for acceptance
* Use Moodle session to store information about acceptance of cookie before even the user is logged in.
* Uses templates.

Notes
--

I hope at some point this plugin will ultimately be integrated natively within Moodle core (and maybe merged with current tool policy features).
There was an issue in tool_policy preventing users from accepting policies that are not mandatory as a guest. This has been resolved temporarily by duplicating the 
view.php, index.php and relevant renderer. A ticket will be opened on this topic and the code will hopefully move there.
